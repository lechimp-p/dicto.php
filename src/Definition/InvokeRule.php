<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition;

class InvokeRule extends Rule {
    /**
     * @var Variable
     */
    private $invokes;

    public function __construct($mode, Variable $left, Variable $invokes) {
        parent::__construct($mode, $left);
        $this->invokes = $invokes;
    }

    public function invoke(FunctionVariable $fun) {
        return InvokeRule($this, $fun);
    }

    /**
     * @return Variable
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

