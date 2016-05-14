<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Definition\Rules;
use \Lechimp\Dicto\Definition\Variables as Vars;
use \Lechimp\Dicto\Rules as R;

class Property extends Rule {
    /**
     * @var array
     */
    private $arguments;

    /**
     * @var R\Property
     */
    private $property;

    /**
     * @param string $mode
     */
    public function __construct($mode, Vars\Variable $left, R\Property $property, array $arguments) {
        parent::__construct($mode, $left);
        $this->arguments = $arguments;
        $this->property = $property;
    }

    // TODO: This seems odd. Its part of a fluid interface, right?
    public function invoke(Functions $fun) {
        return InvokeRule($this, $fun);
    }

    /**
     * @return Vars\Variable
     */
    public function right() {
        return $this->right;
    }

    /**
     * @inheritdoc
     */
    public function explain($text) {
        $r = new Relation($this->mode(), $this->subject(), $this->right, $this->relation);
        $r->setExplanation($text);
        return $r;
    }

    /**
     * @inheritdoc
     */
    public function variables() {
        return array($this->subject(), $this->right);
    }

    /**
     * @inheritdoc
     */
    public function schema() {
        return  $this->property;
    }

    // TODO: This should go to Rule.
    public function argument($num) {
        return $this->arguments[$num];
    }
}

