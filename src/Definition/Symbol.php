<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 * 
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition;

/**
 * A symbol in known to the parser.
 */
class Symbol {
    /**
     * @var string
     */
    protected $regexp;

    /**
     * @var int
     */
    protected $binding_power;

    /**
     * This defines what a token means when appearing in the initial position
     * of an expression.
     *
     * @var \Closure
     */
    protected $null_denotation = null;

    /**
     * This defines what a token means when appearing inside an expression
     * to the left of the rest.
     *
     * @var \Closure
     */
    protected $left_denotation = null;

    public function __construct($regexp, $binding_power) {
        assert('is_string($regexp)');
        if (!is_string($regexp) || @preg_match("%$regexp%", "") === false) {
            throw new \InvalidArgumentException("Invalid regexp: '%$regexp%'");
        }
        assert('is_int($binding_power)');
        assert('$binding_power >= 0');
        $this->regexp = $regexp;
        $this->binding_power = $binding_power;
    }

    /**
     * @return  string
     */
    public function regexp() {
        return $this->regexp;
    }

    /**
     * @return  int
     */
    public function binding_power() {
        return $this->binding_power;
    }

    /**
     * @param   \Closure    $led
     * @return  self
     */
    public function null_denotation_is(\Closure $led) {
        assert('$this->null_denotation === null');
        $this->null_denotation = $led;
        return $this;
    }

    /**
     * @param   array   $matches
     * @return  mixed
     */
    public function null_denotation(array &$matches) {
        $denotation = $this->denotation("null", $matches); 
        return $denotation($matches);
    }

    /**
     * @param   \Closure    $led
     * @return  self
     */
    public function left_denotation_is(\Closure $led) {
        assert('$this->left_denotation === null');
        $this->left_denotation = $led;
        return $this;
    }

    /**
     * @param   mixed   $left
     * @param   array   $matches
     * @return  mixed
     */
    public function left_denotation($left, array &$matches) {
        $denotation = $this->denotation("left", $matches); 
        return $denotation($left, $matches);
    }

    // HELPER

    protected function denotation($which, array &$matches) {
        $which = $which."_denotation";
        $denotation = $this->$which;
        if ($this->$which === null) {
            $m = $matches[0];
            throw new ParserException("Syntax Error: $m");
        }
        return $denotation;
    }
}
