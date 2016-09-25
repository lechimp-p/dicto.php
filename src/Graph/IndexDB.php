<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

use Lechimp\Dicto\Indexer\Insert;

/**
 * A database for the indexer based on graph.
 */
class IndexDB extends Graph implements Insert {
    /**
     * @var array<string,Node>
     */
    protected $globals = array();

    /**
     * @inheritdocs
     */
    public function _file($path, $source) {
        assert('is_string($path)');
        assert('is_string($source)');
        return $this->create_node
            ( "file"
            ,   [ "path" => $path
                , "source" => explode("\n", $source)
                ]
            );
    }

    /**
     * @inheritdocs
     */
    public function _class($name, $file, $start_line, $end_line) {
        assert('is_string($name)');
        assert('$file->type() == "file"');
        assert('is_int($start_line)');
        assert('is_int($end_line)');
        $class = $this->create_node
            ( "class"
            ,   [ "name" => $name
                ]
            );
        $this->add_relation
            ( $class
            , "defined in"
            ,   [ "start_line" => $start_line
                , "end_line" => $end_line
                ]
            , $file
            );
        return $class;
    }

    /**
     * @inheritdocs
     */
    public function _method($name, $class, $file, $start_line, $end_line) {
        assert('is_string($name)');
        assert('$class->type() == "class"');
        assert('$file->type() == "file"');
        assert('is_int($start_line)');
        assert('is_int($end_line)');
        $method = $this->create_node
            ( "method"
            ,   [ "name" => $name
                ]
            );
        $this->add_relation
            ( $method
            , "defined in"
            ,   [ "start_line" => $start_line
                , "end_line" => $end_line
                ]
            , $file
            );
        $this->add_relation($method, "contained in", [], $class);
        $this->add_relation($class, "contains", [], $method);

        return $method;
    }

    /**
     * @inheritdocs
     */
    public function _function($name, $file, $start_line, $end_line) {
        assert('is_string($name)');
        assert('$file->type() == "file"');
        assert('is_int($start_line)');
        assert('is_int($end_line)');
        $method = $this->create_node
            ( "function"
            ,   [ "name" => $name
                ]
            );
        $this->add_relation
            ( $method
            , "defined in"
            ,   [ "start_line" => $start_line
                , "end_line" => $end_line
                ]
            , $file
            );

        return $method;
    }

    /**
     * @inheritdocs
     */
    public function _global($name) {
        if (array_key_exists($name, $this->globals)) {
            return $this->globals[$name];
        }
        $global = $this->create_node("global", ["name" => $name]);
        $this->globals[$name] = $global;
        return $global;
    }

    /**
     * @inheritdocs
     */
    public function _language_construct($name, $file, $line) {}

    /**
     * @inheritdocs
     */
    public function _method_reference($name, $file, $line) {}

    /**
     * @inheritdocs
     */
    public function _function_reference($name, $file, $line) {}

    /**
     * @inheritdocs
     */
    public function _relation($left_entity, $right_entity, $file, $line) {}

    /**
     * Build a query on the index.
     *
     * @return  IndexQuery
     */
    public function query() {
        return new IndexQueryImpl($this);
    }
}
