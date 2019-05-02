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
     * @var mixed[]
     */
    protected $class_interface_trait = [];

    /**
     * @var mixed[]
     */
    protected $function_method = [];

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
        $c = count($this->class_interface_trait);
        if ($c == 0) {
            return null;
        }
        return $this->class_interface_trait[$c-1];
    }

    /**
     * @inheritdocs
     */
    public function _function_method() {
        $c = count($this->function_method);
        assert('$c == 0 || $c == 1 || $c = 2');
        if ($c == 0) {
            return null;
        }
        return $this->function_method[$c-1];
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
            $this->class_interface_trait[] = $handle;
        }
        else if (in_array($type, [Variable::METHOD_TYPE, Variable::FUNCTION_TYPE])) {
            $this->function_method[] = $handle;
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
        if (count($this->function_method) > 0) {
            array_pop($this->function_method);
        }
        else if (count($this->class_interface_trait) > 0) {
            array_pop($this->class_interface_trait);
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

