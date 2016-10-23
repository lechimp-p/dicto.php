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

/**
 * TODO: This is not the correct name for this thing. It also holds
 * information about the file.
 */
class LocationImpl implements Location {
    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $file_content;

    /**
     * @var int[]|null
     */
    protected $running_line_length = null;

    /**
     * This contains the stack of ids were currently in, i.e. the nesting of
     * known code blocks we are in.
     *
     * @var array   contains ($definition_type, $definition_id) tuples
     */
    protected $definition_stack;

    /**
     * @var \PhpParser\Node|null
     */
    protected $current_node = null;

    public function __construct($file_name, $file_content) {
        assert('is_string($file_name)');
        assert('is_string($file_content)');
        $this->file_name = $file_name;
        $this->file_content = $file_content;
        $this->definition_stack = [];
    }

    /**
     * @return  mixed
     */
    public function file() {
        assert('count($this->definition_stack) > 0');
        assert('$this->definition_stack[0][0] === \\Lechimp\\Dicto\\Variables\\Variable::FILE_TYPE');
        return $this->definition_stack[0][1];
    }

    /**
     * @return  string
     */
    public function file_name() {
        return $this->file_name;
    }

    /**
     * @return  string
     */
    public function file_content() {
        return $this->file_content;
    }

    /**
     * @return  int
     */
    public function line() {
        assert('$this->current_node !== null');
        return $this->current_node->getAttribute("startLine");
    }

    /**
     * @return  int
     */
    public function column() {
        assert('$this->current_node !== null');
        if ($this->running_line_length === null) {
            $this->init_running_line_length();
        }
        $start_pos = $this->current_node->getAttribute("startFilePos");
        $length_before = $this->running_line_length[$this->line() - 1];
        return $start_pos - $length_before + 1;
    }

    /**
     * @return  array[]     List of ($type, $handle)
     */
    public function in_entities() {
        return $this->definition_stack;
    }

    /**
     * @param   int     $pos
     * @return  array       ($type, $handle)
     */
    public function in_entity($pos) {
        assert('is_int($pos)');
        assert('$pos >= 0');
        assert('$pos < count($this->definition_stack)');
        return $this->definition_stack[$pos];
    }

    /**
     * @return   int
     */
    public function count_in_entity() {
        return count($this->definition_stack);
    }

    /**
     * Push an entity on the stack.
     *
     * @param   string  $type
     * @param   mixed   $handle
     * @return  null
     */
    public function push_entity($type, $handle) {
        assert('\\Lechimp\\Dicto\\Variables\\Variable::is_type($type)');
        $this->definition_stack[] = [$type, $handle];
    }

    /**
     * Pop an entity from the stach
     *
     * @return null
     */
    public function pop_entity() {
        array_pop($this->definition_stack);
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

