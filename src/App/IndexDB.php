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
    protected $nodes_per_insert = 200;

    /**
     * @var array
     */
    protected $tables =
        [ "files" => ["id", "path", "source"]
        , "namespaces" => ["id", "name"]
        , "classes" => ["id", "name", "file_id", "start_line", "end_line", "namespace_id"]
        , "interfaces" => ["id", "name", "file_id", "start_line", "end_line", "namespace_id"]
        , "traits" => ["id", "name", "file_id", "start_line", "end_line", "namespace_id"]
        , "methods" => ["id", "name", "class_id", "file_id", "start_line", "end_line"]
        , "functions" => ["id", "name", "file_id", "start_line", "end_line", "namespace_id"]
        , "globals" => ["id", "name"]
        , "language_constructs" => ["id", "name"]
        , "method_references" => ["id", "name", "file_id", "line", "column"]
        , "function_references" => ["id", "name", "file_id", "line", "column"]
        , "relations" => ["left_id", "relation", "right_id", "file_id", "line"]
        ];

    /**
     * @var array[]
     */
    protected $caches = [];

    /**
     * @var integer
     */
    protected $id_counter = 0;

    public function __construct($connection) {
        parent::__construct($connection);
        foreach ($this->tables as $table => $_) {
            $this->caches[$table] = [];
        }
    }

    /**
     * @inheritdocs
     */
    public function _file($path, $source) {
        return $this->append_and_maybe_flush("files",
            [null, $this->esc_str($path), $this->esc_str($source)]);
    }

    /**
     * @inheritdocs
     */
    public function _namespace($name) {
        return $this->append_and_maybe_flush("namespaces",
            [null, $this->esc_str($name)]);
    }

    /**
     * @inheritdocs
     */
    public function _class($name, $file, $start_line, $end_line, $namespace = null) {
        return $this->append_and_maybe_flush("classes",
            [null, $this->esc_str($name), $file, $start_line, $end_line, $this->esc_maybe_null($namespace)]);
    }

    /**
     * @inheritdocs
     */
    public function _interface($name, $file, $start_line, $end_line, $namespace = null) {
        return $this->append_and_maybe_flush("interfaces",
            [null, $this->esc_str($name), $file, $start_line, $end_line, $this->esc_maybe_null($namespace)]);
    }

    /**
     * @inheritdocs
     */
    public function _trait($name, $file, $start_line, $end_line, $namespace = null) {
        return $this->append_and_maybe_flush("traits",
            [null, $this->esc_str($name), $file, $start_line, $end_line, $this->esc_maybe_null($namespace)]);
    }

    /**
     * @inheritdocs
     */
    public function _method($name, $class, $file, $start_line, $end_line) {
        return $this->append_and_maybe_flush("methods",
            [null, $this->esc_str($name), $class, $file, $start_line, $end_line]);
    }

    /**
     * @inheritdocs
     */
    public function _function($name, $file, $start_line, $end_line, $namespace = null) {
        return $this->append_and_maybe_flush("functions",
            [null, $this->esc_str($name), $file, $start_line, $end_line, $this->esc_maybe_null($namespace)]);
    }

    /**
     * @inheritdocs
     */
    public function _global($name) {
        return $this->append_and_maybe_flush("globals",
            [null, $this->esc_str($name)]);
    }

    /**
     * @inheritdocs
     */
    public function _language_construct($name) {
        return $this->append_and_maybe_flush("language_constructs",
            [null, $this->esc_str($name)]);
    }

    /**
     * @inheritdocs
     */
    public function _method_reference($name, $file, $line, $column) {
        return $this->append_and_maybe_flush("method_references",
            [null, $this->esc_str($name), $file, $line, $column]);
    }

    /**
     * @inheritdocs
     */
    public function _function_reference($name, $file, $line, $column) {
        return $this->append_and_maybe_flush("function_references",
            [null, $this->esc_str($name), $file, $line, $column]);
    }

    /**
     * @inheritdocs
     */
    public function _relation($left_entity, $relation, $right_entity, $file, $line) {
        $this->append_and_maybe_flush("relations",
            [$left_entity, $this->esc_str($relation), $right_entity, $file, $line]);
    }

    protected function insert_cache($table) {
        assert('array_key_exists($table, $this->tables)');
        $fields = $this->tables[$table];
        $which = &$this->caches[$table];
        if (count($which) == 0) {
            return;
        }
        $stmt = "INSERT INTO $table (".implode(", ", $fields).") VALUES\n";
        $values = [];
        foreach ($which as $v) {
            $values[] = "(".implode(", ", $v).")";
        }
        $stmt .= implode(",\n", $values).";";
        $this->connection->exec($stmt);
        $which = [];
    }

    protected function append_and_maybe_flush($table, $values) {
        if ($values[0] === null) {
            $id = $this->id_counter++;
            $values[0] = $id;
        }
        else {
            $id = null;
        }

        $which = &$this->caches[$table];
        $which[] = $values;
        if (count($which) > $this->nodes_per_insert) {
            $this->insert_cache($table);
        }

        return $id;
    }
    protected function esc_str($str) {
        assert('is_string($str)');
        return '"'.str_replace('"', '""', $str).'"';
    }

    protected function esc_maybe_null($val) {
        assert('!is_string($val)');
        if ($val === null) {
            return "NULL";
        }
        return $val;
    }

    /**
     * Write everything that currently is in cache to the database.
     *
     * @return null
     */
    public function write_cached_inserts() {
        foreach ($this->tables as $table => $_) {
            $this->insert_cache($table);
        }
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
        $results = [];
        foreach ($this->tables as $key => $_) {
            if ($key == "relations") {
                continue;
            }
            $results[] = [$key, null, $this->select_all_from($key)];
        }

        $count = count($results);
        $i = 0;
        $expected_id = 0;
        // TODO: This will loop forever if there are (unexpected) holes
        // in the sequence of ids.
        while ($count > 0) {
            if ($i >= $count) {
                $i = 0;
            }

            $current_res = $results[$i][1];
            if ($current_res !== null) {
                if ($current_res["id"] != $expected_id) {
                    $i++;
                    continue;
                }

                $current_res["_which"] = $results[$i][0];
                yield $current_res;
                $results[$i][1] = null;
                $expected_id++;
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

        $relations = $this->select_all_from("relations");
        while($res = $relations->fetch()) {
            $res["_which"] = "relations";
            yield $res;
        }
    }

    protected function select_all_from($table) {
        return $this->builder()
            ->select($this->tables[$table])
            ->from($table)
            ->execute();
    }

    protected function write_inserts_to(Graph\IndexDB $index, \Iterator $inserts) {
        $id = -1;
        $id_map = [];
        foreach ($inserts as $insert) {
            $id++;
            assert('$insert["_which"] == "relations" || $insert["id"] == $id');
            if (isset($insert["namespace_id"])) {
                $insert["namespace_id"] = $id_map[(int)$insert["namespace_id"]];
            }
            if (isset($insert["file_id"])) {
                $insert["file_id"] = $id_map[(int)$insert["file_id"]];
            }
            switch ($insert["_which"]) {
                case "files":
                    $id_map[$id] = $index->_file($insert["path"], $insert["source"]);
                    break;
                case "namespaces":
                    $id_map[$id] = $index->_namespace($insert["name"]);
                    break;
                case "classes":
                    $id_map[$id] = $index->_class
                        ( $insert["name"]
                        , $insert["file_id"]
                        , (int)$insert["start_line"]
                        , (int)$insert["end_line"]
                        , $insert["namespace_id"]
                        );
                    break;
                case "interfaces":
                    $id_map[$id] = $index->_interface
                        ( $insert["name"]
                        , $insert["file_id"]
                        , (int)$insert["start_line"]
                        , (int)$insert["end_line"]
                        , $insert["namespace_id"]
                        );
                    break;
                case "traits":
                    $id_map[$id] = $index->_trait
                        ( $insert["name"]
                        , $insert["file_id"]
                        , (int)$insert["start_line"]
                        , (int)$insert["end_line"]
                        , $insert["namespace_id"]
                        );
                    break;
                case "methods":
                    $id_map[$id] = $index->_method
                        ( $insert["name"]
                        , $id_map[(int)$insert["class_id"]]
                        , $insert["file_id"]
                        , (int)$insert["start_line"]
                        , (int)$insert["end_line"]
                        );
                    break;
                case "functions":
                    $id_map[$id] = $index->_function
                        ( $insert["name"]
                        , $insert["file_id"]
                        , (int)$insert["start_line"]
                        , (int)$insert["end_line"]
                        , $insert["namespace_id"]
                        );
                    break;
                case "globals":
                    $id_map[$id] = $index->_global($insert["name"]);
                    break;
                case "language_constructs":
                    $id_map[$id] = $index->_language_construct($insert["name"]);
                    break;
                case "method_references":
                    $id_map[$id] = $index->_method_reference
                        ( $insert["name"]
                        , $insert["file_id"]
                        , (int)$insert["line"]
                        , (int)$insert["column"]
                        );
                    break;
                case "function_references":
                    $id_map[$id] = $index->_function_reference
                        ( $insert["name"]
                        , $insert["file_id"]
                        , (int)$insert["line"]
                        , (int)$insert["column"]
                        );
                    break;
                case "relations":
                    $index->_relation
                        ( $id_map[(int)$insert["left_id"]]
                        , $insert["relation"]
                        , $id_map[(int)$insert["right_id"]]
                        , $insert["file_id"]
                        , (int)$insert["line"]
                        );
                    break;
                default:
                    throw new \LogicException("Can't insert '".$insert["_which"]."'");
            }
        }
    }

    // INIT DATABASE

    public function init_table($name, Schema\Schema $schema, Schema\Table $file_table = null, Schema\Table $namespace_table = null) {
        assert('array_key_exists($name, $this->tables)');
        $table = $schema->createTable($name);
        foreach ($this->tables[$name] as $field) {
            switch ($field) {
                case "id":
                case "file_id":
                case "class_id":
                case "left_id":
                case "right_id":
                case "start_line":
                case "end_line":
                case "line":
                case "column":
                    $table->addColumn
                        ($field, "integer"
                        , ["notnull" => true, "unsigned" => true]
                        );
                    break;
                case "namespace_id":
                    $table->addColumn
                        ($field, "integer"
                        , ["notnull" => false, "unsigned" => true]
                        );
                   break;
                case "path":
                case "source":
                case "name":
                case "relation":
                    $table->addColumn
                        ($field, "string"
                        , ["notnull" => true]
                        );
                    break;
                default:
                    throw new \LogicException("Unknown field '$field'");
            }
            if ($field == "id") {
                $table->setPrimaryKey(["id"]);
            }
            if ($field == "file_id") {
                $table->addForeignKeyConstraint
                    ( $file_table
                    , array("file_id")
                    , array("id")
                    );
            }
            if ($field == "namespace_id") {
                $table->addForeignKeyConstraint
                    ( $namespace_table
                    , array("namespace_id")
                    , array("id")
                    );
            }
        }
        return $table;
    }

    public function init_database_schema() {
        $schema = new Schema\Schema();

        $file_table = $this->init_table("files", $schema);
        $namespace_table = $this->init_table("namespaces", $schema);
        $this->init_table("classes", $schema, $file_table, $namespace_table);
        $this->init_table("interfaces", $schema, $file_table, $namespace_table);
        $this->init_table("traits", $schema, $file_table, $namespace_table);
        $this->init_table("methods", $schema, $file_table);
        $this->init_table("functions", $schema, $file_table, $namespace_table);
        $this->init_table("globals", $schema);
        $this->init_table("language_constructs", $schema);
        $this->init_table("method_references", $schema, $file_table);
        $this->init_table("function_references", $schema, $file_table);
        $relation_table = $this->init_table("relations", $schema, $file_table);
        $relation_table->setPrimaryKey(["left_id", "relation", "right_id"]);

        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }
}
