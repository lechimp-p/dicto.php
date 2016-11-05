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
use Lechimp\Dicto\Indexer\Insert;
use Doctrine\DBAL\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;
use Doctrine\DBAL\Statement;

class IndexDB extends DB implements Insert {
    protected $insert_per_transaction = 1000;

    /**
     * @var integer
     */
    protected $id_counter = 0;

    /**
     * @inheritdocs
     */
    public function _file($path, $source) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("files")
            ->values(
                [ "id" => "?"
                , "path" => "?"
                , "source" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $path)
            ->setParameter(2, $source)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _namespace($name) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("namespaces")
            ->values(
                [ "id" => "?"
                , "name" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $name)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _class($name, $file, $start_line, $end_line, $namespace = null) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("classes")
            ->values(
                [ "id" => "?"
                , "name" => "?"
                , "file_id" => "?"
                , "start_line" => "?"
                , "end_line" => "?"
                , "namespace_id" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $name)
            ->setParameter(2, $file)
            ->setParameter(3, $start_line)
            ->setParameter(4, $end_line)
            ->setParameter(5, $namespace)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _interface($name, $file, $start_line, $end_line, $namespace = null) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("interfaces")
            ->values(
                [ "id" => "?"
                , "name" => "?"
                , "file_id" => "?"
                , "start_line" => "?"
                , "end_line" => "?"
                , "namespace_id" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $name)
            ->setParameter(2, $file)
            ->setParameter(3, $start_line)
            ->setParameter(4, $end_line)
            ->setParameter(5, $namespace)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _trait($name, $file, $start_line, $end_line, $namespace = null) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("traits")
            ->values(
                [ "id" => "?"
                , "name" => "?"
                , "file_id" => "?"
                , "start_line" => "?"
                , "end_line" => "?"
                , "namespace_id" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $name)
            ->setParameter(2, $file)
            ->setParameter(3, $start_line)
            ->setParameter(4, $end_line)
            ->setParameter(5, $namespace)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _method($name, $class, $file, $start_line, $end_line) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("methods")
            ->values(
                [ "id" => "?"
                , "name" => "?"
                , "class_id" => "?"
                , "file_id" => "?"
                , "start_line" => "?"
                , "end_line" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $name)
            ->setParameter(2, $class)
            ->setParameter(3, $file)
            ->setParameter(4, $start_line)
            ->setParameter(5, $end_line)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _function($name, $file, $start_line, $end_line, $namespace = null) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("functions")
            ->values(
                [ "id" => "?"
                , "name" => "?"
                , "file_id" => "?"
                , "start_line" => "?"
                , "end_line" => "?"
                , "namespace_id" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $name)
            ->setParameter(2, $file)
            ->setParameter(3, $start_line)
            ->setParameter(4, $end_line)
            ->setParameter(5, $namespace)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _global($name) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("globals")
            ->values(
                [ "id" => "?"
                , "name" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $name)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _language_construct($name) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("language_constructs")
            ->values(
                [ "id" => "?"
                , "name" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $name)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _method_reference($name, $file, $line, $column) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("method_references")
            ->values(
                [ "id" => "?"
                , "name" => "?"
                , "file_id" => "?"
                , "line" => "?"
                , "column" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $name)
            ->setParameter(2, $file)
            ->setParameter(3, $line)
            ->setParameter(4, $column)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _function_reference($name, $file, $line, $column) {
        $id = $this->id_counter++;
        $this->builder()
            ->insert("function_references")
            ->values(
                [ "id" => "?"
                , "name" => "?"
                , "file_id" => "?"
                , "line" => "?"
                , "column" => "?"
                ])
            ->setParameter(0, $id)
            ->setParameter(1, $name)
            ->setParameter(2, $file)
            ->setParameter(3, $line)
            ->setParameter(4, $column)
            ->execute();
        return $id;
    }

    /**
     * @inheritdocs
     */
    public function _relation($left_entity, $relation, $right_entity, $file, $line) {
        $this->builder()
            ->insert("relations")
            ->values(
                [ "left_id" => "?"
                , "relation" => "?"
                , "right_id" => "?"
                , "file_id" => "?"
                , "line" => "?"
                ])
            ->setParameter(0, $left_entity)
            ->setParameter(1, $relation)
            ->setParameter(2, $right_entity)
            ->setParameter(3, $file)
            ->setParameter(4, $line)
            ->execute();
    }

    /**
     * Write everything that currently is in cache to the database.
     *
     * @return null
     */
    public function write_cached_inserts() {
    }

    /**
     * Read the index from the database.
     *
     * @return  Graph\IndexDB   $index
     */
    public function to_graph_index() {
        $index = $this->build_graph_index_db();
        $inserts = $this->get_inserts();
        $this->write_inserts_to($index, $inserts);
        return $index;
    }

    protected function build_graph_index_db() {
        return new Graph\IndexDB();
    }

    protected function get_inserts() {
        $results =
            [ ["file", null, $this->select_files()]
            , ["namespace", null, $this->select_namespaces()]
            , ["class", null, $this->select_classes()]
            , ["interface", null, $this->select_interfaces()]
            , ["trait", null, $this->select_traits()]
            , ["method", null, $this->select_methods()]
            , ["function", null, $this->select_functions()]
            , ["global", null, $this->select_globals()]
            , ["language_construct", null, $this->select_language_constructs()]
            , ["method_reference", null, $this->select_method_references()]
            , ["function_reference", null, $this->select_function_references()]
            ];
        $count = count($results);
        $i = 0;
        $current_id = 0;
        while ($count > 0) {
            if ($i > $count) {
                $i = 0;
            }

            $current_res = $results[$i][1];
            if ($current_res !== null) {
                if ($current_res["id"] != $current_id) {
                    $i++;
                    continue;
                }

                $current_res["_which"] = $results[$i][0];
                yield $current_res;
                $results[$i][1] = null;
                $current_id++;
            }

            $next_res = $results[$i][2]->fetch();
            if (!$next_res) {
                $count--;
                unset($results[$i]);
                $results = array_values($results);
                continue;
            }
            $results[$i][1] = $next_res;
        }

        $relations = $this->select_relations();
        while($res = $relations->fetch()) {
            $res["_which"] = "relation";
            yield $res;
        }
    }

    protected function select_files() {
        return $this->builder()
            ->select(["id", "path", "source"])
            ->from("files")
            ->execute();
    }

    protected function select_namespaces() {
        return $this->builder()
            ->select(["id", "name"])
            ->from("namespaces")
            ->execute();
    }

    protected function select_classes() {
        return $this->builder()
            ->select(["id", "name", "file_id", "start_line", "end_line", "namespace_id"])
            ->from("classes")
            ->execute();
    }

    protected function select_interfaces() {
        return $this->builder()
            ->select(["id", "name", "file_id", "start_line", "end_line", "namespace_id"])
            ->from("interfaces")
            ->execute();
    }

    protected function select_traits() {
        return $this->builder()
            ->select(["id", "name", "file_id", "start_line", "end_line", "namespace_id"])
            ->from("traits")
            ->execute();
    }

    protected function select_methods() {
        return $this->builder()
            ->select(["id", "name", "class_id", "file_id", "start_line", "end_line"])
            ->from("methods")
            ->execute();
    }

    protected function select_functions() {
        return $this->builder()
            ->select(["id", "name", "file_id", "start_line", "end_line", "namespace_id"])
            ->from("functions")
            ->execute();
    }

    protected function select_globals() {
        return $this->builder()
            ->select(["id", "name"])
            ->from("globals")
            ->execute();
    }

    protected function select_language_constructs() {
        return $this->builder()
            ->select(["id", "name"])
            ->from("language_constructs")
            ->execute();
    }

    protected function select_method_references() {
        return $this->builder()
            ->select(["id", "name", "file_id", "line", "column"])
            ->from("method_references")
            ->execute();
    }

    protected function select_function_references() {
        return $this->builder()
            ->select(["id", "name", "file_id", "line", "column"])
            ->from("function_references")
            ->execute();
    }

    protected function select_relations() {
        return $this->builder()
            ->select(["left_id", "relation", "right_id", "file_id", "line"])
            ->from("relations")
            ->execute();
    }

    protected function write_inserts_to(Graph\IndexDB $index, \Iterator $inserts) {
        $id = -1;
        foreach ($inserts as $insert) {
            $id++;
            assert('$insert["_which"] == "relation" || $insert["id"] == $id');
            switch ($insert["_which"]) {
                case "file":
                    $index->_file($insert["path"], $insert["source"]);
                    break;
                case "namespace":
                    $index->_namespace($insert["name"]);
                    break;
                case "class":
                    $index->_class
                        ( $insert["name"]
                        , $index->node((int)$insert["file_id"])
                        , (int)$insert["start_line"]
                        , (int)$insert["end_line"]
                        , $insert["namespace_id"]
                        );
                    break;
                case "interface":
                    $index->_interface
                        ( $insert["name"]
                        , $index->node((int)$insert["file_id"])
                        , (int)$insert["start_line"]
                        , (int)$insert["end_line"]
                        , $insert["namespace_id"]
                        );
                    break;
                case "trait":
                    $index->_trait
                        ( $insert["name"]
                        , $index->node((int)$insert["file_id"])
                        , (int)$insert["start_line"]
                        , (int)$insert["end_line"]
                        , $insert["namespace_id"]
                        );
                    break;
                case "method":
                    $index->_method
                        ( $insert["name"]
                        , $index->node((int)$insert["class_id"])
                        , $index->node((int)$insert["file_id"])
                        , (int)$insert["start_line"]
                        , (int)$insert["end_line"]
                        );
                    break;
                case "function":
                    $index->_function
                        ( $insert["name"]
                        , $index->node((int)$insert["file_id"])
                        , (int)$insert["start_line"]
                        , (int)$insert["end_line"]
                        , $insert["namespace_id"]
                        );
                    break;
                case "global":
                    $index->_global($insert["name"]);
                    break;
                case "language_construct":
                    $index->_language_construct($insert["name"]);
                    break;
                case "method_reference":
                    $index->_method_reference
                        ( $insert["name"]
                        , $index->node((int)$insert["file_id"])
                        , (int)$insert["line"]
                        , (int)$insert["column"]
                        );
                    break;
                case "function_reference":
                    $index->_function_reference
                        ( $insert["name"]
                        , $index->node((int)$insert["file_id"])
                        , (int)$insert["line"]
                        , (int)$insert["column"]
                        );
                    break;
                case "relation":
                    $index->_relation
                        ( $index->node((int)$insert["left_id"])
                        , $insert["relation"]
                        , $index->node((int)$insert["right_id"])
                        , $index->node((int)$insert["file_id"])
                        , (int)$insert["line"]
                        );
                    break;
                default:
                    \LogicException("Can't insert '".$insert["which"]."'");
            }
        }
    }

    // INIT DATABASE

    public function init_file_table(Schema\Schema $schema) {
        $file_table = $schema->createTable("files");
        $file_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $file_table->addColumn
            ( "path", "string"
            , ["notnull" => true]
            );
        $file_table->addColumn
            ( "source", "string"
            , ["notnull" => true]
            );
        $file_table->setPrimaryKey(["id"]);
        return $file_table;
    }

    public function init_namespace_table(Schema\Schema $schema) {
        $namespace_table = $schema->createTable("namespaces");
        $namespace_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $namespace_table->addColumn
            ( "name", "string"
            , ["notnull" => true]
            );
        $namespace_table->setPrimaryKey(["id"]);
        return $namespace_table;
    }

    public function init_class_table(Schema\Schema $schema, Schema\Table $file_table, Schema\Table $namespace_table) {
        $class_table = $schema->createTable("classes");
        $class_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $class_table->addColumn
            ( "name", "string"
            , ["notnull" => true]
            );
        $class_table->addColumn
            ( "file_id", "integer"
            , ["notnull" => true]
            );
        $class_table->addColumn
            ( "start_line", "integer"
            , ["notnull" => true]
            );
        $class_table->addColumn
            ( "end_line", "integer"
            , ["notnull" => true]
            );
        $class_table->addColumn
            ( "namespace_id", "integer"
            , ["notnull" => false]
            );
        $class_table->setPrimaryKey(["id"]);
        $class_table->addForeignKeyConstraint
            ( $file_table
            , array("file_id")
            , array("id")
            );
        $class_table->addForeignKeyConstraint
            ( $namespace_table
            , array("namespace_id")
            , array("id")
            );
        return $class_table;
    }

    public function init_interface_table(Schema\Schema $schema, Schema\Table $file_table, Schema\Table $namespace_table) {
        $interface_table = $schema->createTable("interfaces");
        $interface_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $interface_table->addColumn
            ( "name", "string"
            , ["notnull" => true]
            );
        $interface_table->addColumn
            ( "file_id", "integer"
            , ["notnull" => true]
            );
        $interface_table->addColumn
            ( "start_line", "integer"
            , ["notnull" => true]
            );
        $interface_table->addColumn
            ( "end_line", "integer"
            , ["notnull" => true]
            );
        $interface_table->addColumn
            ( "namespace_id", "integer"
            , ["notnull" => false]
            );
        $interface_table->setPrimaryKey(["id"]);
        $interface_table->addForeignKeyConstraint
            ( $file_table
            , array("file_id")
            , array("id")
            );
        $interface_table->addForeignKeyConstraint
            ( $namespace_table
            , array("namespace_id")
            , array("id")
            );
    }

    public function init_trait_table(Schema\Schema $schema, Schema\Table $file_table, Schema\Table $namespace_table) {
        $trait_table = $schema->createTable("traits");
        $trait_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $trait_table->addColumn
            ( "name", "string"
            , ["notnull" => true]
            );
        $trait_table->addColumn
            ( "file_id", "integer"
            , ["notnull" => true]
            );
        $trait_table->addColumn
            ( "start_line", "integer"
            , ["notnull" => true]
            );
        $trait_table->addColumn
            ( "end_line", "integer"
            , ["notnull" => true]
            );
        $trait_table->addColumn
            ( "namespace_id", "integer"
            , ["notnull" => false]
            );
        $trait_table->setPrimaryKey(["id"]);
        $trait_table->addForeignKeyConstraint
            ( $file_table
            , array("file_id")
            , array("id")
            );
        $trait_table->addForeignKeyConstraint
            ( $namespace_table
            , array("namespace_id")
            , array("id")
            );
    }

    public function init_method_table(Schema\Schema $schema, Schema\Table $file_table, Schema\Table $class_table) {
        $method_table = $schema->createTable("methods");
        $method_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $method_table->addColumn
            ( "name", "string"
            , ["notnull" => true]
            );
        $method_table->addColumn
            ( "class_id", "integer"
            , ["notnull" => true]
            );
        $method_table->addColumn
            ( "file_id", "integer"
            , ["notnull" => true]
            );
        $method_table->addColumn
            ( "start_line", "integer"
            , ["notnull" => true]
            );
        $method_table->addColumn
            ( "end_line", "integer"
            , ["notnull" => true]
            );
        $method_table->setPrimaryKey(["id"]);
        $method_table->addForeignKeyConstraint
            ( $class_table
            , array("class_id")
            , array("id")
            );
        $method_table->addForeignKeyConstraint
            ( $file_table
            , array("file_id")
            , array("id")
            );
    }

    public function init_function_table(Schema\Schema $schema, Schema\Table $file_table, Schema\Table $namespace_table) {
        $function_table = $schema->createTable("functions");
        $function_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $function_table->addColumn
            ( "name", "string"
            , ["notnull" => true]
            );
        $function_table->addColumn
            ( "file_id", "integer"
            , ["notnull" => true]
            );
        $function_table->addColumn
            ( "start_line", "integer"
            , ["notnull" => true]
            );
        $function_table->addColumn
            ( "end_line", "integer"
            , ["notnull" => true]
            );
        $function_table->addColumn
            ( "namespace_id", "integer"
            , ["notnull" => false]
            );
        $function_table->setPrimaryKey(["id"]);
        $function_table->addForeignKeyConstraint
            ( $file_table
            , array("file_id")
            , array("id")
            );
        $function_table->addForeignKeyConstraint
            ( $namespace_table
            , array("namespace_id")
            , array("id")
            );
    }

    public function init_global_table(Schema\Schema $schema) {
        $global_table = $schema->createTable("globals");
        $global_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $global_table->addColumn
            ( "name", "string"
            , ["notnull" => true]
            );
        $global_table->setPrimaryKey(["id"]);
    }

    public function init_language_construct_table(Schema\Schema $schema) {
        $language_construct_table = $schema->createTable("language_constructs");
        $language_construct_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $language_construct_table->addColumn
            ( "name", "string"
            , ["notnull" => true]
            );
        $language_construct_table->setPrimaryKey(["id"]);
    }

    public function init_method_reference_table(Schema\Schema $schema, Schema\Table $file_table) {
        $method_reference_table = $schema->createTable("method_references");
        $method_reference_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $method_reference_table->addColumn
            ( "name", "string"
            , ["notnull" => true]
            );
        $method_reference_table->addColumn
            ( "file_id", "integer"
            , ["notnull" => true]
            );
        $method_reference_table->addColumn
            ( "line", "integer"
            , ["notnull" => true]
            );
        $method_reference_table->addColumn
            ( "column", "integer"
            , ["notnull" => true]
            );
        $method_reference_table->setPrimaryKey(["id"]);
        $method_reference_table->addForeignKeyConstraint
            ( $file_table
            , array("file_id")
            , array("id")
            );
    }

    public function init_function_reference_table(Schema\Schema $schema, Schema\Table $file_table) {
        $function_reference_table = $schema->createTable("function_references");
        $function_reference_table->addColumn
            ("id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $function_reference_table->addColumn
            ( "name", "string"
            , ["notnull" => true]
            );
        $function_reference_table->addColumn
            ( "file_id", "integer"
            , ["notnull" => true]
            );
        $function_reference_table->addColumn
            ( "line", "integer"
            , ["notnull" => true]
            );
        $function_reference_table->addColumn
            ( "column", "integer"
            , ["notnull" => true]
            );
        $function_reference_table->setPrimaryKey(["id"]);
        $function_reference_table->addForeignKeyConstraint
            ( $file_table
            , array("file_id")
            , array("id")
            );
    }

    public function init_relation_table(Schema\Schema $schema, Schema\Table $file_table) {
        $relation_table = $schema->createTable("relations");
        $relation_table->addColumn
            ("left_id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $relation_table->addColumn
            ( "relation", "string"
            , ["notnull" => true]
            );
        $relation_table->addColumn
            ("right_id", "integer"
            , ["notnull" => true, "unsigned" => true]
            );
        $relation_table->addColumn
            ( "file_id", "integer"
            , ["notnull" => true]
            );
        $relation_table->addColumn
            ( "line", "integer"
            , ["notnull" => true]
            );
        $relation_table->setPrimaryKey(["left_id", "relation", "right_id"]);
        $relation_table->addForeignKeyConstraint
            ( $file_table
            , array("file_id")
            , array("id")
            );
    }

    public function init_database_schema() {
        $schema = new Schema\Schema();

        $file_table = $this->init_file_table($schema);
        $namespace_table = $this->init_namespace_table($schema);
        $class_table = $this->init_class_table($schema, $file_table, $namespace_table);
        $this->init_interface_table($schema, $file_table, $namespace_table);
        $this->init_trait_table($schema, $file_table, $namespace_table);
        $this->init_method_table($schema, $file_table, $class_table);
        $this->init_function_table($schema, $file_table, $namespace_table);
        $this->init_global_table($schema);
        $this->init_language_construct_table($schema);
        $this->init_method_reference_table($schema, $file_table);
        $this->init_function_reference_table($schema, $file_table);
        $this->init_relation_table($schema, $file_table);

        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }
}
