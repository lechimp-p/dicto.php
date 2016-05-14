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

class Relation extends Rule {
    /**
     * @var Vars\Variable
     */
    private $right;

    /**
     * @param string $mode
     */
    public function __construct($mode, Vars\Variable $left, Vars\Variable $right, R\Relation $relation) {
        parent::__construct($mode, $left, $relation);
        $this->right = $right;
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
        $r = new Relation($this->mode(), $this->subject(), $this->right, $this->schema());
        $r->setExplanation($text);
        return $r;
    }

    /**
     * @inheritdoc
     */
    public function variables() {
        return array($this->subject(), $this->right);
    }
}

