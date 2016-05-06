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
use Lechimp\Dicto\Definition\Variables as Vars;

class Invoke extends Rule {
    /**
     * @var Variable
     */
    private $invokes;

    /**
     * @param string $mode
     */
    public function __construct($mode, Vars\Variable $left, Vars\Variable $invokes) {
        parent::__construct($mode, $left);
        $this->invokes = $invokes;
    }

    // TODO: This seems odd. Its part of a fluid interface, right?
    public function invoke(Functions $fun) {
        return InvokeRule($this, $fun);
    }

    /**
     * @return Vars\Variable
     */
    public function invokes() {
        return $this->invokes;
    }

    /**
     * @inheritdoc
     */
    public function explain($text) {
        $r = new InvokeRule($this->mode(), $this->subject(), $this->invokes);
        $r->setExplanation($text);
        return $r;
    }
}

