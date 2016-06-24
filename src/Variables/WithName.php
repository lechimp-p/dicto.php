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

/**
 * Another variable that has a certain name.
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
        if (!is_string($regexp) || @preg_match("%$regexp%", "") === false) {
            throw new \InvalidArgumentException("Invalid regexp: '%$regexp%'");
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
}
