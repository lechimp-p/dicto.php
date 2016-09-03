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
use PhpParser\Node as N;
use Psr\Log\LoggerInterface as Log;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Lechimp\Flightcontrol\Flightcontrol;
use Lechimp\Flightcontrol\File;
use Lechimp\Flightcontrol\FSObject;

/**
 * Creates an index of source files.
 */
class Indexer implements Location, ListenerRegistry, \PhpParser\NodeVisitor {
    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Insert
     */
    protected $insert;

    /**
     * @var \PhpParser\Parser
     */
    protected $parser;

    /**
     * @var array   string => array()
     */
    protected $listeners_enter_definition;

    /**
     * @var array   string => array()
     */
    protected $listeners_leave_definition;

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
     * @var string|null
     */
    protected $file_content = null;

    /**
     * This contains the stack of ids were currently in, i.e. the nesting of
     * known code blocks we are in.
     *
     * @var array|null  contain ($definition_type, $definition_id)
     */
    protected $definition_stack = null;

    public function __construct(Log $log, \PhpParser\Parser $parser, Insert $insert) {
        $this->log = $log;
        $this->parser = $parser;
        $this->insert = $insert;
        $this->listeners_enter_definition = array
            ( 0 => array()
            );
        $this->listeners_leave_definition = array
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
     * Index a directory.
     *
     * @param   string  $path
     * @param   array   $ignore_paths
     * @return  null
     */
    public function index_directory($path, array $ignore_paths) {
        $fc = $this->init_flightcontrol($path);
        $fc->directory("/")
            ->recurseOn()
            ->filter(function(FSObject $obj) use (&$ignore_paths) {
                foreach ($ignore_paths as $pattern) {
                    if (preg_match("%$pattern%", $obj->path()) !== 0) {
                        return false;
                    }
                }
                return true;
            })
            ->foldFiles(null, function($_, File $file) use ($path) {
                try {
                    $this->index_file($path, $file->path());
                }
                catch (\PhpParser\Error $e) {
                    $this->log->error("in ".$file->path().": ".$e->getMessage());
                }
            });

    }

    /**
     * Initialize the filesystem abstraction.
     *
     * @return  Flightcontrol
     */
    public function init_flightcontrol($path) {
        $adapter = new Local($path, LOCK_EX, Local::SKIP_LINKS);
        $flysystem = new Filesystem($adapter);
        return new Flightcontrol($flysystem);
    }

    /**
     * @param   string  $base_dir
     * @param   string  $path
     * @return  null
     */
    public function index_file($base_dir, $path) {
        assert('is_string($base_dir)');
        assert('is_string($path)');
        $this->log->info("indexing: ".$path);
        $full_path = "$base_dir/$path";
        $content = file_get_contents($full_path);
        if ($content === false) {
            throw \InvalidArgumentException("Can't read file $path.");
        }
        $this->index_content($path, $content);
    }

    /**
     * @param   string  $path
     * @param   string  $content
     * @return  null
     */
    public function index_content($path, $content) {
        assert('is_string($path)');
        assert('is_string($content)');

        $stmts = $this->parser->parse($content);
        if ($stmts === null) {
            throw new \RuntimeException("Can't parse file $path.");
        }

        $traverser = new \PhpParser\NodeTraverser;
        $traverser->addVisitor($this);

        $this->definition_stack = array();
        $this->file_path = $path;
        $this->file_content = $content;
        $traverser->traverse($stmts);
    }

   // from ListenerRegistry 

    /**
     * @inheritdoc
     */
    public function on_enter_definition($types, \Closure $listener) {
        $this->on_enter_or_leave_something("listeners_enter_definition", $types, $listener);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function on_leave_definition($types, \Closure $listener) {
        $this->on_enter_or_leave_something("listeners_leave_definition", $types, $listener);
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

    /**
     * @param   string      $what
     * @param   array|null  $things
     */
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

    /**
     * @param   string          $which
     * @param   \PhpParser\Node $node
     */
    protected function call_misc_listener($which, \PhpParser\Node $node) {
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

    /**
     * @param   string                  $which
     * @param   string                  $type
     * @param   int                     $type
     * @param   \PhpParser\Node|null    $node
     */
    protected function call_definition_listener($which, $type, $id, \PhpParser\Node $node = null) {
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
    public function in_entities() {
        return $this->definition_stack;
    }

    // from \PhpParser\NodeVisitor

    /**
     * @inheritdoc
     */
    public function beforeTraverse(array $nodes) {
        $id = $this->insert->source($this->file_path, $this->file_content);

        return null;
    }

    /**
     * @inheritdoc
     */
    public function afterTraverse(array $nodes) {
        list($type, $id) = array_pop($this->definition_stack);

        return null;
    }

    /**
     * @inheritdoc
     */
    public function enterNode(\PhpParser\Node $node) {
        $start_line = $node->getAttribute("startLine");
        $end_line = $node->getAttribute("endLine");

        if ($this->is_definition($node)) {
            $type = $this->get_type_of($node);
            $id = $this->insert->definition
                ( $node->name
                , $type
                , $this->file_path
                , $start_line
                , $end_line
                );
            $this->call_definition_listener("listeners_enter_definition",  $type, $id, $node);
            $this->definition_stack[] = array($type, $id);
        }
        else {
            $this->call_misc_listener("listeners_enter_misc", $node);
        }
    }

    protected function get_type_of(\PhpParser\Node $node) {
        // Class
        if ($node instanceof N\Stmt\Class_) {
            return Variable::CLASS_TYPE;
        }
        // Method or Function
        elseif ($node instanceof N\Stmt\ClassMethod) {
            return Variable::METHOD_TYPE;
        }
        elseif ($node instanceof N\Stmt\Function_) {
            return Variable::FUNCTION_TYPE;
        }
        throw \InvalidArgumentException("'".get_class($node)."' has no type.");
    }

    protected function is_definition(\PhpParser\Node $node) {
        return     $node instanceof N\Stmt\Class_
                || $node instanceof N\Stmt\ClassMethod
                || $node instanceof N\Stmt\Function_;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(\PhpParser\Node $node) {
        // Class
/*        if($this->is_definition($node)) {
            list($type, $id) = array_pop($this->definition_stack);
            $this->call_definition_listener("listeners_leave_definition", $type, $id, $node);
        }
        else {
            $this->call_misc_listener("listeners_leave_misc", $node);
        }*/
    }
}
