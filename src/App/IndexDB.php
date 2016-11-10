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
        [ "files" => ["_file",
            ["id", "path", "source"]]
        , "namespaces" => ["_namespace",
            ["id", "name"]]
        , "classes" => ["_class",
            ["id", "name", "file_id", "start_line", "end_line", "namespace_id"]]
        , "interfaces" => ["_interface",
            ["id", "name", "file_id", "start_line", "end_line", "namespace_id"]]
        , "traits" => ["_trait",
            ["id", "name", "file_id", "start_line", "end_line", "namespace_id"]]
        , "methods" => ["_method",
            ["id", "name", "class_id", "file_id", "start_line", "end_line"]]
        , "functions" => ["_function",
            ["id", "name", "file_id", "start_line", "end_line", "namespace_id"]]
        , "globals" => ["_global",
            ["id", "name"]]
        , "language_constructs" => ["_language_construct",
            ["id", "name"]]
        , "method_references" => ["_method_reference",
            ["id", "name", "file_id", "line", "column"]]
        , "function_references" => ["_function_reference",
            ["id", "name", "file_id", "line", "column"]]
        , "relations" => ["_relation",
            ["left_id", "relation", "right_id", "file_id", "line"]]
        ];

    /**
     * Lists of fields that contain an id.
     *
     * @var array<string,int>
     */
    protected $id_fields =
        [ "file_id" => 0
        , "namespace_id" => 0
        , "class_id" => 0
        , "left_id" => 0
        , "right_id" => 0
        ];

    /**
     * List of all fields that contain an integer.
     *
     * @var array<string,0>
     */
    protected $int_fields =
        [ "start_line" => 0
        , "end_line" => 0
        , "line" => 0
        , "column" => 0
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
        $fields = $this->tables[$table][1];
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

    /**
     * Builds a list of inserts ordered by the id.
     *
     * @return  \Iterator   $table => $values
     */
    protected function get_inserts() {
        $results = $this->build_results_with_id(); 

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

                yield $results[$i][0] => $current_res;
                $results[$i][1] = null;
                $expected_id++;
            }

            $rs = $results[$i][2];
            if ($rs->valid()) {
                $results[$i][1] = $rs->current();
                $rs->next();
            }
            else {
                $count--;
                unset($results[$i]);
                $results = array_values($results);
            }
        }

        $rs = $this->select_all_from("relations");
        while ($rs->valid()) {
            yield "relations" => $rs->current();
            $rs->next();
        }
    }

    /**
     * Initialize all tables that contain an id with their results.
     *
     * @return array[]  containing [$table_name, null, $results]
     */
    protected function build_results_with_id() {
        $results = [];
        foreach ($this->tables as $key => $_) {
            if ($key == "relations") {
                continue;
            }
            $results[] = [$key, null, $this->select_all_from($key)];
        }
        return $results;
    }

    /**
     * @return  \Iterator
     */
    protected function select_all_from($table) {
        $query_result = $this->builder()
            ->select($this->tables[$table][1])
            ->from($table)
            ->execute();
        return (function() use ($query_result) {
            while ($res = $query_result->fetch()) {
                yield $res;
            }
        })();
    }

    /**
     * Transforms results as retreived from sql to their appropriate internal
     * representation.
     *
     * @param   array   $id_map      to resolve ids
     * @param   array   &$results    from database
     * @return  null
     */
    public function transform_results(array $id_map, array &$results) {
        foreach (array_keys($results) as $field) {
            if (isset($this->id_fields[$field])) {
                $results[$field] = $id_map[$results[$field]];
            }
            else if (isset($this->int_fields[$field])) {
                $results[$field] = (int)$results[$field];
            }
        }
    }

    protected function write_inserts_to(Graph\IndexDB $index, \Iterator $inserts) {
        $id_map = [null => null];
        foreach ($inserts as $table => $insert) {
            $this->transform_results($id_map, $insert);

            assert('array_key_exists($table, $this->tables)');
            $method = $this->tables[$table][0];
            if (isset($insert["id"])) {
                $id = $insert["id"];
                unset($insert["id"]);
                $id_map[$id] = call_user_func_array([$index, $method], $insert);
            }
            else {
                call_user_func_array([$index, $method], $insert);
            }
        }
    }

    // INIT DATABASE

    public function init_table($name, Schema\Schema $schema, Schema\Table $file_table = null, Schema\Table $namespace_table = null) {
        assert('array_key_exists($name, $this->tables)');
        $table = $schema->createTable($name);
        foreach ($this->tables[$name][1] as $field) {
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
