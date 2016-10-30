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

use Lechimp\Dicto\Variables\Variable;

/**
 * TODO: This is not the correct name for this thing. It also holds
 * information about the file.
 */
class LocationImpl implements Location {
    /**
     * @var string
     */
    protected $file_name;

    /**
     * @var string
     */
    protected $file_content;

    /**
     * @var int[]|null
     */
    protected $running_line_length = null;

    /**
     * @var mixed|null
     */
    protected $file = null;

    /**
     * @var mixed|null
     */
    protected $namespace = null;

    /**
     * @var mixed|null
     */
    protected $class_interface_trait = null;

    /**
     * @var mixed|null
     */
    protected $function_method = null;

    /**
     * @var \PhpParser\Node|null
     */
    protected $current_node = null;

    public function __construct($file_name, $file_content) {
        assert('is_string($file_name)');
        assert('is_string($file_content)');
        $this->file_name = $file_name;
        $this->file_content = $file_content;
    }

    /**
     * @inheritdocs
     */
    public function _file() {
        return $this->file;
    }

    /**
     * @inheritdocs
     */
    public function _namespace() {
        return $this->namespace;
    }

    /**
     * @inheritdocs
     */
    public function _class_interface_trait() {
        return $this->class_interface_trait;
    }

    /**
     * @inheritdocs
     */
    public function _function_method() {
        return $this->function_method;
    }

    /**
     * @return  string
     */
    public function _file_name() {
        return $this->file_name;
    }

    /**
     * @return  string
     */
    public function _file_content() {
        return $this->file_content;
    }

    /**
     * @return  int
     */
    public function _line() {
        assert('$this->current_node !== null');
        return $this->current_node->getAttribute("startLine");
    }

    /**
     * @return  int
     */
    public function _column() {
        assert('$this->current_node !== null');
        if ($this->running_line_length === null) {
            $this->init_running_line_length();
        }
        $start_pos = $this->current_node->getAttribute("startFilePos");
        $length_before = $this->running_line_length[$this->_line() - 1];
        return $start_pos - $length_before + 1;
    }

    /**
     * Push an entity on the stack.
     *
     * @param   string  $type
     * @param   mixed   $handle
     * @return  null
     */
    public function push_entity($type, $handle) {
        if ($type == Variable::FILE_TYPE) {
            $this->file = $handle;
        }
        else if ($type == Variable::NAMESPACE_TYPE) {
            $this->namespace = $handle;
        }
        else if (in_array($type, [Variable::CLASS_TYPE, Variable::INTERFACE_TYPE, Variable::TRAIT_TYPE])) {
            $this->class_interface_trait = $handle;
        }
        else if (in_array($type, [Variable::METHOD_TYPE, Variable::FUNCTION_TYPE])) {
            $this->function_method = $handle;
        }
        else {
            throw new \LogicException("What should i do with handles of type '$type'?");
        }
    }

    /**
     * Pop an entity from the stach
     *
     * @return null
     */
    public function pop_entity() {
        if ($this->function_method !== null) {
            $this->function_method = null;
        }
        else if ($this->class_interface_trait !== null) {
            $this->class_interface_trait = null;
        }
        else if ($this->namespace !== null) {
            $this->namespace = null;
        }
        else if ($this->file !== null) {
            $this->file = null;
        }
        else {
            throw new \LogicException("Can't pop anymore entities.");
        }
    }

    /**
     * @param   \PhpParser\Node $node
     * @return  null
     */
    public function set_current_node(\PhpParser\Node $node) {
        assert('$this->current_node === null');
        $this->current_node = $node;
    }

    /**
     * @return  \PhpParser\Node $node
     */
    public function current_node() {
        assert('$this->current_node !== null');
        return $this->current_node;
    }

    /**
     * @return  null
     */
    public function flush_current_node() {
        assert('$this->current_node !== null');
        $this->current_node = null;
    }

    protected function init_running_line_length() {
        $pos = 0;
        $count = 0;
        while (true) {
            $this->running_line_length[] = $count;
            $count = strpos($this->file_content, "\n", $pos);
            if (!$count) {
                break;
            }
            $count++; // for actual linebreak
            $pos = $count;
        }
    }
}

