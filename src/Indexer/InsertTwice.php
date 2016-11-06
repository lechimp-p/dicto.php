<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Indexer;

use Lechimp\Dicto\Analysis\Variable;

/**
 * Duplicate inserts, return handles from both interfaces and analyses them
 * on usage.
 */
class InsertTwice implements Insert {
    /**
     * @var Insert
     */
    public $insert1;

    /**
     * @var Insert
     */
    public $insert2;

    public function __construct(Insert $insert1, Insert $insert2) {
        $this->insert1 = $insert1;
        $this->insert2 = $insert2;
    }

    /**
     * @return Insert
     */
    public function first() {
        return $this->insert1;
    }

    /**
     * @return Insert
     */
    public function second() {
        return $this->insert2;
    }

    /**

    /**
     * @inheritdocs
     */
    public function _file($path, $source) {
        $id1 = $this->insert1->_file($path, $source);
        $id2 = $this->insert2->_file($path, $source);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _namespace($name) {
        $id1 = $this->insert1->_namespace($name);
        $id2 = $this->insert2->_namespace($name);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _class($name, $file, $start_line, $end_line, $namespace = null) {
        $id1 = $this->insert1->_class($name, $file[0], $start_line, $end_line, $namespace[0]);
        $id2 = $this->insert2->_class($name, $file[1], $start_line, $end_line, $namespace[1]);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _interface($name, $file, $start_line, $end_line, $namespace = null) {
        $id1 = $this->insert1->_interface($name, $file[0], $start_line, $end_line, $namespace[0]);
        $id2 = $this->insert2->_interface($name, $file[1], $start_line, $end_line, $namespace[1]);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _trait($name, $file, $start_line, $end_line, $namespace = null) {
        $id1 = $this->insert1->_trait($name, $file[0], $start_line, $end_line, $namespace[0]);
        $id2 = $this->insert2->_trait($name, $file[1], $start_line, $end_line, $namespace[1]);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _method($name, $class, $file, $start_line, $end_line) {
        $id1 = $this->insert1->_method($name, $class[0], $file[0], $start_line, $end_line);
        $id2 = $this->insert2->_method($name, $class[1], $file[1], $start_line, $end_line);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _function($name, $file, $start_line, $end_line, $namespace = null) {
        $id1 = $this->insert1->_function($name, $file[0], $start_line, $end_line, $namespace[0]);
        $id2 = $this->insert2->_function($name, $file[1], $start_line, $end_line, $namespace[1]);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _global($name) {
        $id1 = $this->insert1->_global($name);
        $id2 = $this->insert2->_global($name);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _language_construct($name) {
        $id1 = $this->insert1->_language_construct($name);
        $id2 = $this->insert2->_language_construct($name);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _method_reference($name, $file, $line, $column) {
        $id1 = $this->insert1->_method_reference($name, $file[0], $line, $column);
        $id2 = $this->insert2->_method_reference($name, $file[1], $line, $column);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _function_reference($name, $file, $line, $column) {
        $id1 = $this->insert1->_function_reference($name, $file[0], $line, $column);
        $id2 = $this->insert2->_function_reference($name, $file[1], $line, $column);
        return [$id1, $id2];
    }

    /**
     * @inheritdocs
     */
    public function _relation($left_entity, $relation, $right_entity, $file, $line) {
        $id1 = $this->insert1->_relation($left_entity[0], $relation, $right_entity[0], $file[0], $line);
        $id2 = $this->insert2->_relation($left_entity[1], $relation, $right_entity[1], $file[1], $line);
        return [$id1, $id2];
    }
}
