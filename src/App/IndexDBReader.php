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

use Lechimp\Dicto\Indexer\Insert;

/**
 * Reads in an sql IndexDB to another Index.
 */
class IndexDBReader {
    /**
     * @var array
     */
    protected $tables;

    /**
     * Lists of fields that contain an id.
     *
     * @var array<string,int>
     */
    protected $id_fields;

    /**
     * List of all fields that contain an integer.
     *
     * @var array<string,0>
     */
    protected $int_fields;

    /**
     * @var \Closure
     */
    protected $get_query_builder;

    /**
     * @var Insert
     */
    protected $other;

    /**
     * @var bool
     */
    protected $already_run;

    /**
     * @param   array   $tables     list of tables and fields according to IndexDB
     * @param   array   $id_fields  list of fields that contain a id
     * @param   array   $int_fields list of fields that contain an int
     * @param   \Closure    $get_query_builder  to get a QueryBuilder
     */
    public function __construct($tables, $id_fields, $int_fields, \Closure $get_query_builder, Insert $insert) {
        $this->tables = $tables;
        $this->id_fields = $id_fields;
        $this->int_fields = $int_fields;
        $this->get_query_builder = $get_query_builder;
        $this->other = $insert;
        $this->already_run = false;
    }


    /**
     * Read the index from the database and put it to insert.
     */
    public function run() {
        assert('!$this->already_run');

        $this->id_map = [null => null];

        $inserts = $this->get_inserts();
        $this->write_inserts($inserts);

        $this->already_run = true;
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

                $this->transform_results($current_res);
                yield $results[$i][0] => $current_res;
                $results[$i][1] = null;
                $expected_id++;
            }

            $res = $results[$i][2]->fetch();
            if ($res) {
                $results[$i][1] = $res;
            }
            else {
                $count--;
                unset($results[$i]);
                $results = array_values($results);
            }
        }

        $rs = $this->select_all_from("relations");
        while ($res = $rs->fetch()) {
            $this->transform_results($res);
            yield "relations" => $res;
        }
    }

    /**
     * Initialize all tables that contain an id with their results.
     *
     * @return  array[] containing [$table_name, null, $results]
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
     * @param   string  $table
     * @return  \Iterator
     */
    protected function select_all_from($table) {
        $get_query_builder = $this->get_query_builder;
        return $get_query_builder()
            ->select($this->tables[$table][1])
            ->from($table)
            ->execute();
    }

    /**
     * Transforms results as retreived from sql to their appropriate internal
     * representation.
     *
     * TODO: Unrolling this loop might make things faster.
     *
     * @param   array   &$results    from database
     * @return  array
     */
    public function transform_results(array &$results) {
        foreach (array_keys($results) as $field) {
            if (isset($this->id_fields[$field])) {
                $results[$field] = $this->id_map[$results[$field]];
            }
            else if (isset($this->int_fields[$field])) {
                $results[$field] = (int)$results[$field];
            }
        }
    }

    /**
     * @param   \Iterator       $inserts
     * @return  null
     */
    protected function write_inserts(\Iterator $inserts) {
        foreach ($inserts as $table => $insert) {
            assert('array_key_exists($table, $this->tables)');
            $method = $this->tables[$table][0];
            if (isset($insert["id"])) {
                $id = $insert["id"];
                unset($insert["id"]);
                $this->id_map[$id] = call_user_func_array([$this->other, $method], $insert);
            }
            else {
                call_user_func_array([$this->other, $method], $insert);
            }
        }
    }
}
