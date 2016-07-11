<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

abstract class Combinator extends Variable {
    /**
     * @var Variable
     */
    private $left;

    /**
     * @var Variable
     */
    private $right;

    public function __construct($name, Variable $left, Variable $right) {
        parent::__construct($name);
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * Get an id for this combinator.
     *
     * This must return a string without whitespaces.
     *
     * @return  string
     */
    abstract public function id();


    /**
     * @inheritdocs
     */
    public function meaning() {
        $cb = str_replace("_", " ", $this->id());
        return $this->left()->meaning()." $cb ".$this->right()->meaning();
    }

    /**
     * @return  Variable
     */
    public function left() {
        return $this->left;
    }

    /**
     * @return  Variable
     */
    public function right() {
        return $this->right;
    }
}
