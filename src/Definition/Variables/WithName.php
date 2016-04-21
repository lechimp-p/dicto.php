<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Definition\Variables;

/**
 * Provides fluid interface to _with.
 */
class WithName extends Variable {
    /**
     * @var string
     */
    private $regexp;

    /**
     * @var Variable
     */
    private $other;

    public function __construct($regexp, Variable $other) {
        parent::__construct($other->name());
        if (!is_string($regexp) or @preg_match("%$regexp%", "") === false) {
            throw new \InvalidArgumentException("Invalid regexp: '%regexp'");
        }
        $this->regexp = $regexp;
        $this->other = $other;
    }

    /**
     * @return  string
     */
    public function regexp() {
        return $this->regexp;
    }

    /**
     * @return  Variable
     */
    public function variable() {
        return $this->other;
    }

    /**
     * @inheritdoc
     */
    public function explain($text) {
        $v = new Classes();
        $v->setExplanation($text);
        return $v;
    }

}
