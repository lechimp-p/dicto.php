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

use Lechimp\Dicto\Regexp;
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
class Indexer implements ListenerRegistry, \PhpParser\NodeVisitor {
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
    protected $listeners_enter_misc;

    /**
     * @var LocationImpl|null
     */
    protected $location = null;

    public function __construct(Log $log, \PhpParser\Parser $parser, Insert $insert) {
        $this->log = $log;
        $this->parser = $parser;
        $this->insert = $insert;
        $this->listeners_enter_definition = array
            ( 0 => array()
            );
        $this->listeners_enter_misc = array
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
        $ignore_paths_re = array_map(function($ignore) {
            return new Regexp($ignore);
        }, $ignore_paths);
        $fc = $this->init_flightcontrol($path);
        $fc->directory("/")
            ->recurseOn()
            ->filter(function(FSObject $obj) use (&$ignore_paths_re) {
                foreach ($ignore_paths_re as $re) {
                    if ($re->match($obj->path())) {
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

        $this->location = new LocationImpl($path, $content);
        $traverser->traverse($stmts);
        $this->location = null;
    }

   // from ListenerRegistry 

    /**
     * @inheritdoc
     */
    public function on_enter_definition($types, \Closure $listener) {
        $this->on_enter_something("listeners_enter_definition", $types, $listener);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function on_enter_misc($classes, \Closure $listener) {
        $this->on_enter_something("listeners_enter_misc", $classes, $listener);
        return $this;
    }

    // generalizes over over on_enter

    /**
     * TODO: Maybe remove this in favour of duplicates.
     *
     * @param   string      $what
     * @param   array|null  $things
     */
    protected function on_enter_something($what, $things, \Closure $listener) {
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
     */
    protected function call_misc_listener($which) {
        $listeners = &$this->$which;
        $current_node = $this->location->current_node();
        foreach ($listeners[0] as $listener) {
            $listener($this->insert, $this->location, $current_node);
        }
        $cls = get_class($current_node);
        if (array_key_exists($cls, $listeners)) {
            foreach ($listeners[$cls] as $listener) {
                $listener($this->insert, $this->location, $current_node);
            }
        }
    }

    /**
     * @param   string                  $which
     * @param   string                  $type
     * @param   int                     $type
     */
    protected function call_definition_listener($which, $type, $id) {
        $listeners = &$this->$which;
        $current_node = $this->location->current_node();
        foreach ($listeners[0] as $listener) {
            $listener($this->insert, $this->location, $type, $id, $current_node);
        }
        if (array_key_exists($type, $listeners)) {
            foreach ($listeners[$type] as $listener) {
                $listener($this->insert, $this->location, $type, $id, $current_node);
            }
        }
    }

    // from \PhpParser\NodeVisitor

    /**
     * @inheritdoc
     */
    public function beforeTraverse(array $nodes) {
        $handle = $this->insert->_file($this->location->_file_name(), $this->location->_file_content());
        $this->location->push_entity(Variable::FILE_TYPE, $handle);
    }

    /**
     * @inheritdoc
     */
    public function afterTraverse(array $nodes) {
    }

    /**
     * @inheritdoc
     */
    public function enterNode(\PhpParser\Node $node) {
        $start_line = $node->getAttribute("startLine");
        $end_line = $node->getAttribute("endLine");

        $this->location->set_current_node($node);

        $handle = null;
        $type = null;
        if ($node instanceof N\Stmt\Namespace_) {
            $handle = $this->insert->_namespace
                ( "".$node->name
                );
            $type = Variable::NAMESPACE_TYPE;
        }
        else if ($node instanceof N\Stmt\Class_) {
            $handle = $this->insert->_class
                ( $node->name
                , $this->location->_file()
                , $start_line
                , $end_line
                , $this->location->_namespace()
                );
            $type = Variable::CLASS_TYPE;
        }
        else if ($node instanceof N\Stmt\Interface_) {
            $handle = $this->insert->_interface
                ( $node->name
                , $this->location->_file()
                , $start_line
                , $end_line
                , $this->location->_namespace()
                );
            $type = Variable::INTERFACE_TYPE;
        }
        else if ($node instanceof N\Stmt\Trait_) {
            $handle = $this->insert->_trait
                ( $node->name
                , $this->location->_file()
                , $start_line
                , $end_line
                , $this->location->_namespace()
                );
            $type = Variable::INTERFACE_TYPE;
        }
        else if ($node instanceof N\Stmt\ClassMethod) {
            $handle = $this->insert->_method
                ( $node->name
                , $this->location->_class_interface_trait()
                , $this->location->_file()
                , $start_line
                , $end_line
                );
            $type = Variable::METHOD_TYPE;
        }
        else if ($node instanceof N\Stmt\Function_) {
            $handle = $this->insert->_function
                ( $node->name
                , $this->location->_file()
                , (int)$start_line
                , (int)$end_line
                );
            $type = Variable::FUNCTION_TYPE;
        }

        if ($handle !== null) {
            assert('$type !== null');
            if ($type !== Variable::NAMESPACE_TYPE) {
               $this->call_definition_listener("listeners_enter_definition",  $type, $handle);
            }
            $this->location->push_entity($type, $handle);
        }
        else {
            assert('$type === null');
            $this->call_misc_listener("listeners_enter_misc");
        }

        $this->location->flush_current_node();
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(\PhpParser\Node $node) {
        if (  $node instanceof N\Stmt\Namespace_
           || $node instanceof N\Stmt\Class_
           || $node instanceof N\Stmt\Interface_
           || $node instanceof N\Stmt\ClassMethod
           || $node instanceof N\Stmt\Function_) {

            $this->location->pop_entity();
        }
    }
}
