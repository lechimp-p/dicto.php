<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition\AST;

/**
 * An except-definition.
 */
class Except extends Definition { 
    /**
     * @var Definition
     */
    protected  $left;

    /**
     * @var Definition
     */
    protected  $right;

    public function __construct(Definition $left, Definition $right) {
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * @return Definition
     */
    public function left() {
        return $this->left;
    }

    /**
     * @return Definition
     */
    public function right() {
        return $this->right;
    }
}
