<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Graph;
use Doctrine\DBAL\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;
use Doctrine\DBAL\Statement;

class IndexDB extends DB {
    protected $insert_per_transaction = 100;

    /**
     * Write the index to the database.
     *
     * @param   Graph\IndexDB   $index
     * @return  null
     */
    public function write_index(Graph\IndexDB $index) {
        $this->prepare_insert_statements();
        $max_id = $this->serialize_nodes($index);
        $this->serialize_relations($index, $max_id);
    }

    /**
     * Read the index from the database.
     *
     * @return  Graph\IndexDB   $index
     */
    public function read_index() {
        $index = $this->build_graph_index_db();
        $this->deserialize_nodes_to($index);
        $this->deserialize_relations_to($index);
        return $index;
    }

    protected function build_graph_index_db() {
        return new Graph\IndexDB();
    }

    // WRITE

    protected function serialize_nodes(Graph\IndexDB $index) {
        $max_id = 0;
        $this->connection->beginTransaction();
        $count = 0;
        foreach ($index->nodes() as $node) {
            $count++;
            $id = $node->id();
            $this->insert_entity_stmt->execute([$node->id(), $node->type()]);
            $max_id = max($max_id, $id);
            $this->insert_properties($id, $node);
            if ($count >= $this->insert_per_transaction) {
                $this->connection->commit();
                $this->connection->beginTransaction();
            }
        }
        $this->connection->commit();
        return $max_id;
    }

    protected function serialize_relations(Graph\IndexDB $index, $id) {
        $this->connection->beginTransaction();
        $count = 0;
        foreach ($index->nodes() as $node) {
            foreach ($node->relations() as $relation) {
                $count++;
                $id++;
                $this->insert_entity_stmt->execute([$id, $relation->type()]);
                $this->insert_properties($id, $relation);
                $this->insert_relation_stmt->execute([$node->id(), $id, $relation->target()->id()]);
                if ($count >= $this->insert_per_transaction) {
                    $this->connection->commit();
                    $this->connection->beginTransaction();
                }
            }
        }
        $this->connection->commit();
    }

    protected $insert_entity_stmt;
    protected $insert_property_stmt;
    protected $insert_relation_stmt;

    protected function prepare_insert_statements() {
        $this->insert_entity_stmt = $this->connection->prepare
            ( "INSERT INTO entities (id, type)\n"
            . "VALUES (?, ?)"
            );
        $this->insert_property_stmt = $this->connection->prepare
            ( "INSERT INTO properties (entity_id, key, is_entity, value)\n"
            . "VALUES (?, ?, ?, ?)"
            );
        $this->insert_relation_stmt = $this->connection->prepare
            ( "INSERT INTO relations (source_id, entity_id, target_id)\n"
            . "VALUES (?, ?, ?)"
            );
    }

    protected function insert_properties($id, Graph\Entity $e) {
        foreach ($e->properties() as $key => $value) {
            $this->insert_property($id, $key, $value);
        }
    }

    protected function insert_property($entity_id, $key, $value) {
        $is_entity = false;
        if ($value instanceof Graph\Node) {
            $is_entity = true;
            $value = $value->id();
        }
        if ($value instanceof Graph\Relation) {
            throw new \LogicException("Can't serialize Relation properties (NYI!)");
        }
        $value = serialize($value);
        $this->insert_property_stmt->execute([$entity_id, $key, $is_entity, $value]);
    }

    // READ

    protected function deserialize_nodes_to(Graph\IndexDB $index) {
        $res = $this->connection->executeQuery
            ( "SELECT entities.id, entities.type FROM entities\n"
            . "LEFT JOIN relations ON entities.id = relations.entity_id\n"
            . "WHERE relations.source_id IS NULL"
            );
        while ($row = $res->fetch()) {
            $properties = $this->select_properties($row["id"], $index);
            $node = $index->create_node($row["type"], $properties);
            assert('$row["id"] == $node->id()');
        }
    }

    protected function deserialize_relations_to(Graph\IndexDB $index) {
        $res = $this->connection->executeQuery
            ( "SELECT source_id, entity_id, target_id, type FROM relations\n"
            . "JOIN entities ON entities.id = relations.entity_id\n"
            . "ORDER BY source_id, entity_id"
            );
        $node = null;
        while ($row = $res->fetch()) {
            if ($node === null || $node->id() != $row["source_id"]) {
                $node = $index->node((int)$row["source_id"]);
            }
            $other = $index->node((int)$row["target_id"]);
            $properties = $this->select_properties($row["entity_id"], $index);
            $node->add_relation($row["type"], $properties, $other);
        }
    }


    protected function select_properties($id, $index) {
        $id = (int)$id;
        $res = $this->connection->executeQuery
            ( "SELECT key, is_entity, value FROM properties\n"
            . "WHERE entity_id = $id"
            );
        $props = [];
        while ($row = $res->fetch()) {
            $value = unserialize($row["value"]);
            if ($row["is_entity"]) {
                $value = $index->node($value);
            }
            $props[$row["key"]] = $value;
        }
        return $props;
    }

    // INIT

    public function init_database_schema() {
        $schema = new Schema\Schema();

        $entity_table = $schema->createTable("entities");
        $entity_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $entity_table->addColumn
            ( "type", "string"
            , ["notnull" => true]
            );
        $entity_table->setPrimaryKey(["id"]);

        $property_table = $schema->createTable("properties");
        $property_table->addColumn
            ("entity_id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $property_table->addColumn
            ( "key", "string"
            , ["notnull" => true]
            );
        $property_table->addColumn
            ( "is_entity", "boolean"
            , ["notnull" => true]
            );
        $property_table->addColumn
            ( "value", "string"
            , ["notnull" => true]
            );
        $property_table->addForeignKeyConstraint
            ( $entity_table
            , array("entity_id")
            , array("id")
            );
        $property_table->setPrimaryKey(["entity_id", "key"]);

        $relation_table = $schema->createTable("relations");
        $relation_table->addColumn
            ( "source_id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $relation_table->addColumn
            ( "entity_id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $relation_table->addColumn
            ( "target_id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $relation_table->addForeignKeyConstraint
            ( $entity_table
            , array("source_id")
            , array("id")
            );
        $relation_table->addForeignKeyConstraint
            ( $entity_table
            , array("entity_id")
            , array("id")
            );
        $relation_table->addForeignKeyConstraint
            ( $entity_table
            , array("target_id")
            , array("id")
            );
        $relation_table->setPrimaryKey(["entity_id"]);

        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }
}
