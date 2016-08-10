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

use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Analysis\CompilesVars;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Indexer\CachesReferences;
use Doctrine\DBAL\Schema as S;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

class IndexDB extends DB implements Insert, Query {
    use CachesReferences;

    // Implementation of Insert interface.

    /**
     * @inheritdoc
     */
    public function source_file($name, $content) {
        assert('is_string($name)');
        assert('is_string($content)');
        $stmt = $this->builder()
            ->insert($this->source_table())
            ->values(array
                ( "file" => "?"
                , "line" => "?"
                , "source" => "?"
                ))
            ->setParameter(0, $name);
        $line = 1;
        foreach (explode("\n", $content) as $source) {
            $stmt
                ->setParameter(1, $line)
                ->setParameter(2, $source)
                ->execute();
            $line++;
        }
    }

    /**
     * @inheritdoc
     */
    public function entity($type, $name, $file, $start_line, $end_line) {
        assert('\\Lechimp\\Dicto\\Variables\\Variable::is_type($type)');
        assert('is_string($name)');
        assert('is_string($file)');
        assert('is_int($start_line)');
        assert('is_int($end_line)');
        $name_id = $this->maybe_insert_name($name, $type);
        $loc_id = $this->get_source_location_id($file, $start_line);
        $this->builder()
            ->insert($this->definition_table())
            ->values(array
                ( "name" => "?"
                , "source_location" => "?"
                , "lines" => "?"
                ))
            ->setParameter(0, $name_id)
            ->setParameter(1, $loc_id)
            ->setParameter(2, $end_line - $start_line + 1)
            ->execute();
        return (int)$this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function reference($type, $name, $file, $line) {
        assert('\\Lechimp\\Dicto\\Variables\\Variable::is_type($type)');
        assert('is_string($name)');
        assert('is_string($file)');
        assert('is_int($line)');
        $name_id = $this->maybe_insert_name($name, $type);
        $loc_id = $this->get_source_location_id($file, $line);
        $this->builder()
            ->insert($this->reference_table())
            ->values(array
                ( "name" => "?"
                , "source_location" => "?"
                ))
            ->setParameter(0, $name_id)
            ->setParameter(1, $loc_id)
            ->execute();
        return (int)$this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function relation($name, $entity_id, $reference_id) {
        assert('is_string($name)');
        assert('is_int($entity_id)');
        assert('is_int($reference_id)');
        $this->builder()
            ->insert($this->relation_table())
            ->values(array
                ( "name_left" => "?"
                , "name_right" => "?"
                , "which" => "?"
                ))
            ->setParameter(0, $entity_id)
            ->setParameter(1, $reference_id)
            ->setParameter(2, $name)
            ->execute();
    }

    protected function name_id($name) {
        $res = $this->builder()
            ->select("id")
            ->from($this->name_table())
            ->where($this->builder()->expr()->andX
                ( "name = ?"
                ))
            ->setParameter(0, $name)
            ->execute()
            ->fetch();
        if ($res) {
            return (int)$res["id"];
        }
        else {
            return null;
        }
    }

    protected function insert_name($name, $type) {
        $this->builder()
            ->insert($this->name_table())
            ->values(array
                ( "name" => "?"
                , "type" => "?"
                ))
            ->setParameter(0, $name)
            ->setParameter(1, $type)
            ->execute();
        return (int)$this->connection->lastInsertId();
    }

    protected function maybe_insert_name($name, $type) {
        $id = $this->name_id($name);
        if ($id === null) {
            return $this->insert_name($name, $type);
        }
        else {
            return $id;
        }
    }

    public function get_source_location_id($file, $line) {
        $b = $this->builder()->expr();
        $res = $this->builder()
            ->select("id")
            ->from($this->source_table())
            ->where
                ( $b->eq("file", $b->literal($file))
                , $b->eq("line", $b->literal($line))
                )
            ->execute()
            ->fetchAll();
        assert('count($res) == 1');
        return (int)$res[0]["id"];
    }

    // Naming

    public function name_table() {
        return "names";
    }

    public function init_name_table(S\Schema $schema) {
        $name_table = $schema->createTable($this->name_table());
        $name_table->addColumn
            ("id", "integer"
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $name_table->addColumn
            ( "name", "string"
            , array("notnull" => true)
            );
        // TODO: insert namespace column here
        $name_table->addColumn
            ( "type", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $name_table->setPrimaryKey(array("id"));
        $name_table->addUniqueIndex(array("name"));
        return $name_table;
    }

    public function source_table() {
        return "source";
    }

    public function init_source_table(S\Schema $schema) {
        $source_table = $schema->createTable($this->source_table());
        $source_table->addColumn
            ("id", "integer"
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $source_table->addColumn
            ( "file", "string"
            , array("notnull" => true)
            );
        $source_table->addColumn
            ( "line", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $source_table->addColumn
            ( "source", "string"
            , array("notnull" => true)
            );
        $source_table->setPrimaryKey(array("id"));
        $source_table->addUniqueIndex(array("file", "line"));
        return $source_table;
    }

    public function definition_table() {
        return "definitions";
    }

    public function init_definition_table(S\Schema $schema, S\Table $name_table, S\Table $source_table) {
        $definition_table = $schema->createTable($this->definition_table());
        $definition_table->addColumn
            ( "name", "integer"
            , array("notnull" => true)
            );
        $definition_table->addColumn
            ( "source_location", "integer"
            , array("notnull" => true)
            );
        $definition_table->addColumn
            ( "lines", "integer"
            , array("notnull" => true)
            );
        $definition_table->setPrimaryKey(array("name"));
        $definition_table->addForeignKeyConstraint
            ( $name_table
            , array("name")
            , array("id")
            );
        $definition_table->addForeignKeyConstraint
            ( $source_table
            , array("source_location")
            , array("id")
            );
        return $definition_table;
    }

    public function reference_table() {
        return "refs";
    }

    public function init_reference_table(S\Schema $schema, S\Table $name_table, S\Table $source_table) {
        $reference_table = $schema->createTable($this->reference_table());
        $reference_table->addColumn
            ( "name", "integer"
            , array("notnull" => true)
            );
        $reference_table->addColumn
            ( "source_location", "integer"
            , array("notnull" => true)
            );
        $reference_table->setPrimaryKey(array("name", "source_location"));
        $reference_table->addForeignKeyConstraint
            ( $name_table
            , array("name")
            , array("id")
            );
        $reference_table->addForeignKeyConstraint
            ( $source_table
            , array("source_location")
            , array("id")
            );
        return $reference_table;
    }

    public function relation_table() {
        return "relations";
    }

    public function init_relation_table(S\Schema $schema, S\Table $name_table) {
        $relation_table = $schema->createTable($this->relation_table());
        $relation_table->addColumn
            ( "name_left", "integer"
            , array("notnull" => true)
            );
        $relation_table->addColumn
            ( "name_right", "integer"
            , array("notnull" => true)
            );
        $relation_table->addColumn
            ( "which", "string"
            , array("notnull" => true)
            );
        $relation_table->setPrimaryKey(array("name_left", "name_right", "which"));
        $relation_table->addForeignKeyConstraint
            ( $name_table
            , array("name_left")
            , array("id")
            );
        $relation_table->addForeignKeyConstraint
            ( $name_table
            , array("name_right")
            , array("id")
            );
        return $relation_table;
    }

    // Creation of database.

    public function init_database_schema() {
        $schema = new S\Schema();

        $name_table = $this->init_name_table($schema);
        $source_table = $this->init_source_table($schema, $name_table);
        $this->init_definition_table($schema, $name_table, $source_table);
        $this->init_reference_table($schema, $name_table, $source_table);
        $this->init_relation_table($schema, $name_table);

        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }
}
