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

use Lechimp\Dicto\Indexer as I;
use Lechimp\Dicto\Variables\Variable;
use PhpParser\Node as N;

/**
 * Implementation of Indexer with PhpParser.
 */
class Indexer implements Location, ListenerRegistry, \PhpParser\NodeVisitor {
    /**
     * @var string
     */
    protected $project_root_path;

    /**
     * @var I\Insert|null
     */
    protected $insert;

    /**
     * @var \PhpParser\Parser
     */
    protected $parser;

    /**
     * @var array   string => array()
     */
    protected $listeners_enter_entity;

    /**
     * @var array   string => array()
     */
    protected $listeners_leave_entity;

    /**
     * @var array   string => array()
     */
    protected $listeners_enter_misc;

    /**
     * @var array   string => array()
     */
    protected $listeners_leave_misc;

    // state for parsing a file

    /**
     * @var string|null
     */
    protected $file_path = null;

    /**
     * @var string[]|null
     */
    protected $file_content = null;

    /**
     * This contains the stack of ids were currently in, i.e. the nesting of
     * known code blocks we are in.
     *
     * @var array|null  contain ($entity_type, $entity_id)
     */
    protected $entity_stack = null;

    /**
     * @param   string  $project_root_path
     * @param   Schema[]  $rule_schemas
     */
    public function __construct(\PhpParser\Parser $parser, $project_root_path, Insert $insert) {
        $this->parser = $parser;
        assert('is_string($project_root_path)');
        $this->project_root_path = $project_root_path;
        $this->insert = $insert;
        $this->listeners_enter_entity = array
            ( 0 => array()
            );
        $this->listeners_leave_entity = array
            ( 0 => array()
            );
        $this->listeners_enter_misc = array
            ( 0 => array()
            );
        $this->listeners_leave_misc = array
            ( 0 => array()
            );
    }

    /**
     * @param   string  $path
     */
    public function index_file($path) {
        if ($this->insert === null) {
            throw new \RuntimeException(
                "Set an inserter to be used before starting to index files.");
        }

        $content = file_get_contents($this->project_root_path."/$path");
        if ($content === false) {
            throw \InvalidArgumentException("Can't read file $path.");
        }

        $stmts = $this->parser->parse($content);
        if ($stmts === null) {
            throw new \RuntimeException("Can't parse file $path.");
        }

        $traverser = new \PhpParser\NodeTraverser;
        $traverser->addVisitor($this);

        $this->entity_stack = array();
        $this->file_path = $path;
        $this->file_content = explode("\n", $content);
        $traverser->traverse($stmts);
        $this->entity_stack = null; 
        $this->file_path = null;
        $this->file_content = null;
    }

    // helper

    private function lines_from_to($start, $end) {
        assert('is_int($start)');
        assert('is_int($end)');
        return implode("\n", array_slice($this->file_content, $start-1, $end-$start+1));
    }

   // from ListenerRegistry 

    /**
     * @inheritdoc
     */
    public function on_enter_entity($types, \Closure $listener) {
        $this->on_enter_or_leave_something("listeners_enter_entity", $types, $listener);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function on_leave_entity($types, \Closure $listener) {
        $this->on_enter_or_leave_something("listeners_leave_entity", $types, $listener);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function on_enter_misc($classes, \Closure $listener) {
        $this->on_enter_or_leave_something("listeners_enter_misc", $classes, $listener);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function on_leave_misc($classes, \Closure $listener) {
        $this->on_enter_or_leave_something("listeners_leave_misc", $classes, $listener);
        return $this;
    }

    // generalizes over over on_enter/leave_xx
    protected function on_enter_or_leave_something($what, $things, \Closure $listener) {
        $loc = &$this->$what;
        if ($things === null) {
            $loc[0][] = $listener;
        }
        else {
            foreach ($things as $thing) {
                assert('is_string($thing)');
                if (!array_key_exists($thing, $loc)) {
                    $loc[$thing] = array();
                }
                $loc[$thing][] = $listener;
            }
        }
    }

    // generalizes over calls to misc listeners
    protected function call_misc_listener($which, $node) {
        $listeners = &$this->$which;
        foreach ($listeners[0] as $listener) {
            $listener($this->insert, $this, $node);
        }
        $cls = get_class($node);
        if (array_key_exists($cls, $listeners)) {
            foreach ($listeners[$cls] as $listener) {
                $listener($this->insert, $this, $node);
            }
        }
    }

    protected function call_entity_listener($which, $type, $id, $node) {
        $listeners = &$this->$which;
        foreach ($listeners[0] as $listener) {
            $listener($this->insert, $this, $type, $id, $node);
        }
        if (array_key_exists($type, $listeners)) {
            foreach ($listeners[$type] as $listener) {
                $listener($this->insert, $this, $type, $id, $node);
            }
        }
    }

   // from Location

    /**
     * @inheritdoc
     */
    public function file_path() {
        return $this->file_path;
    }

    /**
     * @inheritdoc
     */
    public function file_content($from_line = null, $to_line = null) {
        if ($from_line !== null) {
            assert('$to_line !== null');
            return $this->lines_from_to($from_line, $to_line);
        }
        else {
            assert('$to_line === null');
            return implode("\n", $this->file_content);
        }
    }

    /**
     * @inheritdoc
     */
    public function in_entities() {
        return $this->entity_stack;
    }

    // from \PhpParser\NodeVisitor

    /**
     * @inheritdoc
     */
    public function beforeTraverse(array $nodes) {
        // for sure found a file
        $id = $this->insert->entity
            ( Variable::FILE_TYPE
            , $this->file_path
            , $this->file_path
            , 1
            , count($this->file_content)
            , implode("\n", $this->file_content)
            );

        $this->entity_stack[] = array(Variable::FILE_TYPE, $id);

        $this->call_entity_listener("listeners_enter_entity", Variable::FILE_TYPE, $id, null);

        return null;
    }

    /**
     * @inheritdoc
     */
    public function afterTraverse(array $nodes) {
        list($type, $id) = array_pop($this->entity_stack);

        $this->call_entity_listener("listeners_leave_entity", $type, $id, null);

        return null;
    }

    /**
     * @inheritdoc
     */
    public function enterNode(\PhpParser\Node $node) {
        $start_line = $node->getAttribute("startLine");
        $end_line = $node->getAttribute("endLine");
        $source = $this->lines_from_to($start_line, $end_line);

        $id = null;
        $type = null;

        // Class
        if ($node instanceof N\Stmt\Class_) {
            $type = Variable::CLASS_TYPE;
            $id = $this->insert->entity
                ( $type
                , $node->name
                , $this->file_path
                , $start_line
                , $end_line
                , $source
                );
        }
        // Method or Function
        elseif ($node instanceof N\Stmt\ClassMethod) {
            $type = Variable::METHOD_TYPE;
            $id = $this->insert->entity
                ( $type
                , $node->name
                , $this->file_path
                , $start_line
                , $end_line
                , $source
                );
        }
        elseif ($node instanceof N\Stmt\Function_) {
            $type = Variable::FUNCTION_TYPE;
            $id = $this->insert->entity
                ( $type 
                , $node->name
                , $this->file_path
                , $start_line
                , $end_line
                , $source
                );
        }

        if ($id !== null) {
            $this->call_entity_listener("listeners_enter_entity",  $type, $id, $node);
            $this->entity_stack[] = array($type, $id);
        }
        else {
            $this->call_misc_listener("listeners_enter_misc", $node);
        }
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(\PhpParser\Node $node) {
        // Class
        if($node instanceof N\Stmt\Class_
        or $node instanceof N\Stmt\ClassMethod
        or $node instanceof N\Stmt\Function_) {
            list($type, $id) = array_pop($this->entity_stack);
            $this->call_entity_listener("listeners_leave_entity", $type, $id, $node);
        }
        else {
            $this->call_misc_listener("listeners_leave_misc", $node);
        }
    }
}
