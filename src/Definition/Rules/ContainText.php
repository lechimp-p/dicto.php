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

class ContainText extends Rule {
    /**
     * @var string
     */
    private $regexp;

    public function __construct($mode, Vars\Variable $var, $regexp) {
        parent::__construct($mode, $var);
        if (!is_string($regexp) || @preg_match("%$regexp%", "") === false) {
            throw new \InvalidArgumentException("Invalid regexp: '%regexp'");
        }
        $this->regexp = $regexp;
    }

    public function invoke(Functions $fun) {
        return InvokeRule($this, $fun);
    }

    /**
     * @return  string
     */
    public function regexp() {
        return $this->regexp;
    }

    /**
     * @inheritdoc
     */
    public function explain($text) {
        $r = new ContainTextRule($this->mode(), $this->subject(), $this->regexp);
        $r->setExplanation($text);
        return $r;
    }

    /**
     * @inheritdoc
     */
    public function variables() {
        return array($this->subject());
    }

    public function schema() {
        throw new \Exception("no..");
    }
}
