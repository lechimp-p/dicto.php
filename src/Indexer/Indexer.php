<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
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
    protected $listeners;

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
        // TODO: This could contain class names from PhpParser as optimisation.
        $this->listeners = array("misc" => array());
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

        if ($stmts === null) {
            throw new \RuntimeException("Could not parse file '$path'.");
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

    public function on_enter_misc(\Closure $listener) {
        $this->listeners["misc"][] = $listener;
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

        // TODO: reimplement this in some other way.
        /*
        foreach ($this->listeners as $listener) {
            $listener->on_enter_file($id, $this->file_path, implode("\n", $this->file_content));
        }
        */

        return null;
    }

    /**
     * @inheritdoc
     */
    public function afterTraverse(array $nodes) {
        $type_and_id = array_pop($this->entity_stack);

        // TODO: reimplement this in some other way.
        /*
        foreach ($this->listeners as $listener) {
            $listener->on_leave_file($type_and_id[1]);
        }
        */

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

            // TODO: reimplement this in some other way.
            /*
            foreach ($this->listeners as $listener) {
                $listener->on_enter_class($id, $node);
            }
            */
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

            // TODO: reimplement this in some other way.
            /*
            foreach ($this->listeners as $listener) {
                $listener->on_enter_method($id, $node);
            }
            */
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

            // TODO: reimplement this in some other way.
            /*
            foreach ($this->listeners as $listener) {
                $listener->on_enter_function($id, $node);
            }
            */
        }
        else {
            foreach ($this->listeners["misc"] as $listener) {
                $listener($this->insert, $this, $node);
            }
        }

        if ($id !== null) {
            $this->entity_stack[] = array($type, $id);
        }
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(\PhpParser\Node $node) {
        // Class
        if ($node instanceof N\Stmt\Class_) {
            // We pushed it, we need to pop it now, as we are leaving the class.
            $type_and_id = array_pop($this->entity_stack);

            // TODO: reimplement this in some other way.
            /*
            foreach ($this->listeners as $listener) {
                $listener->on_leave_class($type_and_id[1]);
            }
            */
        }
        // Method or Function
        elseif ($node instanceof N\Stmt\ClassMethod) {
            // We pushed it, we need to pop it now, as we are leaving the method
            // or function.
            $type_and_id = array_pop($this->entity_stack);

            // TODO: reimplement this in some other way.
            /*
            foreach ($this->listeners as $listener) {
                $listener->on_leave_method($type_and_id[1]);
            }
            */
        }
        elseif ($node instanceof N\Stmt\Function_) {
            // We pushed it, we need to pop it now, as we are leaving the method
            // or function.
            $type_and_id = array_pop($this->entity_stack);

            // TODO: reimplement this in some other way.
            /*
            foreach ($this->listeners as $listener) {
                $listener->on_leave_function($type_and_id[1]);
            }
            */
        }
        else {
            // TODO: reimplement this in some other way.
            /*
            foreach ($this->listeners as $listener) {
                $listener->on_leave_misc($this->insert, $this, $node);
            }
            */
        }
    }
}
