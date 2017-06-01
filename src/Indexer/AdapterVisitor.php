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
 * Visitor that wraps an ASTVisitor to make it compatible with PhpParser.
 */
class AdapterVisitor implements \PhpParser\NodeVisitor {
    /**
     * @var LocationImpl
     */
    protected $location;

    /**
     * @var Insert
     */
    protected $insert;

    /**
     * @var ASTVisitor
     */
    protected $visitor;

    /**
     * This is used to exit early in enterNode and to break enterNode apart
     * into many methods.
     *
     * @var array<string,string>
     */
    protected $jump_labels;

    public function __construct(LocationImpl $location, Insert $insert, ASTVisitor $visitor) {
        $this->location = $location;
        $this->insert = $insert;
        $this->visitor = $visitor;
        $this->jump_labels = $visitor->visitorJumpLabels();
        assert('$this->jump_labels_are_correct()');
    }

    protected function jump_labels_are_correct() {
        if (!is_array($this->jump_labels)) {
            return false;
        }
        foreach ($this->jump_labels as $label => $method) {
            if (!is_callable(array($this->visitor, $method))) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function beforeTraverse(array $nodes) {
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

        $cls = get_class($node);
        if (isset($this->jump_labels[$cls])) {
            $this->location->set_current_node($node);
            $name = $this->jump_labels[$cls];
            $this->visitor->$name($this->insert, $this->location, $node);
            $this->location->flush_current_node();
        }
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(\PhpParser\Node $node) {
    }
}
