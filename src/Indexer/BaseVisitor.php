<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Indexer;

use Lechimp\Dicto\Variables\Variable;
use PhpParser\Node as N;

/**
 * Visitor for the AST used by Indexer.
 */
class BaseVisitor implements \PhpParser\NodeVisitor
{
    /**
     * @var LocationImpl
     */
    protected $location;

    /**
     * @var Insert
     */
    protected $insert;

    /**
     * This is used to exit early in enterNode and to break enterNode apart
     * into many methods.
     *
     * @var array<string,string>
     */
    protected $jump_labels;

    public function __construct(LocationImpl $location, Insert $insert)
    {
        $this->location = $location;
        $this->insert = $insert;
        $this->jump_labels =
            [ N\Stmt\Namespace_::class => "enterNamespace"
            , N\Stmt\Class_::class => "enterClass"
            , N\Stmt\Interface_::class => "enterInterface"
            , N\Stmt\Trait_::class => "enterTrait"
            , N\Stmt\ClassMethod::class => "enterMethod"
            , N\Stmt\Function_::class => "enterFunction"
            ];
    }

    // from \PhpParser\NodeVisitor

    /**
     * @inheritdoc
     */
    public function beforeTraverse(array $nodes)
    {
        $handle = $this->insert->_file($this->location->_file_name(), $this->location->_file_content());
        $this->location->push_entity(Variable::FILE_TYPE, $handle);
    }

    /**
     * @inheritdoc
     */
    public function afterTraverse(array $nodes)
    {
    }

    /**
     * @inheritdoc
     */
    public function enterNode(\PhpParser\Node $node)
    {
        $cls = get_class($node);
        if (isset($this->jump_labels[$cls])) {
            $this->location->set_current_node($node);
            $start_line = $node->getAttribute("startLine");
            $end_line = $node->getAttribute("endLine");
            list($type, $handle) = $this->{$this->jump_labels[$cls]}
                                                ($node, $start_line, $end_line);
            $this->location->push_entity($type, $handle);
            $this->location->flush_current_node();
        }
    }

    public function enterNamespace(N\Stmt\Namespace_ $node, $start_line, $end_line)
    {
        $handle = $this->insert->_namespace(
                "" . $node->name // force string representation
            );
        return [Variable::NAMESPACE_TYPE, $handle];
    }

    public function enterClass(N\Stmt\Class_ $node, $start_line, $end_line)
    {
        $handle = $this->insert->_class(
                $node->name,
                $this->location->_file(),
                $start_line,
                $end_line,
                $this->location->_namespace()
            );
        return [Variable::CLASS_TYPE, $handle];
    }

    public function enterInterface(N\Stmt\Interface_ $node, $start_line, $end_line)
    {
        $handle = $this->insert->_interface(
                $node->name,
                $this->location->_file(),
                $start_line,
                $end_line,
                $this->location->_namespace()
            );
        return [Variable::INTERFACE_TYPE, $handle];
    }

    public function enterTrait(N\Stmt\Trait_ $node, $start_line, $end_line)
    {
        $handle = $this->insert->_trait(
                $node->name,
                $this->location->_file(),
                $start_line,
                $end_line,
                $this->location->_namespace()
            );
        return [Variable::INTERFACE_TYPE, $handle];
    }

    public function enterMethod(N\Stmt\ClassMethod $node, $start_line, $end_line)
    {
        $handle = $this->insert->_method(
                $node->name,
                $this->location->_class_interface_trait(),
                $this->location->_file(),
                $start_line,
                $end_line
            );
        return [Variable::METHOD_TYPE, $handle];
    }

    public function enterFunction(N\Stmt\Function_ $node, $start_line, $end_line)
    {
        $handle = $this->insert->_function(
                $node->name,
                $this->location->_file(),
                $start_line,
                $end_line
            );
        return [Variable::FUNCTION_TYPE, $handle];
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(\PhpParser\Node $node)
    {
        if ($node instanceof N\Stmt\Namespace_
           || $node instanceof N\Stmt\Class_
           || $node instanceof N\Stmt\Interface_
           || $node instanceof N\Stmt\ClassMethod
           || $node instanceof N\Stmt\Function_) {
            $this->location->pop_entity();
        }
    }
}
