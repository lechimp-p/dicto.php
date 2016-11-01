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

/**
 * Visitor for the AST used by Indexer.
 */
class ASTVisitor implements \PhpParser\NodeVisitor {
    /**
     * @var LocationImpl
     */
    protected $location;

    /**
     * @var Insert
     */
    protected $insert;

    /**
     * @var array   string => array()
     */
    protected $listeners_enter_definition;

    /**
     * @var array   string => array()
     */
    protected $listeners_enter_misc;

    public function __construct(LocationImpl $location, Insert $insert, array &$listeners_enter_definition, array &$listeners_enter_misc) {
        $this->location = $location;
        $this->insert = $insert;
        $this->listeners_enter_definition = $listeners_enter_definition;
        $this->listeners_enter_misc = $listeners_enter_misc;
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
                ( "".$node->name // force string representation
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
