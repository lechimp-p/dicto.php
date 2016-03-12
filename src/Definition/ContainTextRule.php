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

class ContainTextRule extends Rule {
    /**
     * @var Variable
     */
    private $var;

    /**
     * @var string
     */
    private $regexp;

    public function __construct($mode, Variable $var, $regexp) {
        parent::__construct($mode);
        if (!is_string($regexp) or @preg_match("%$regexp%", "") === false) {
            throw new \InvalidArgumentException("Invalid regexp: '%regexp'");
        }
        $this->var = $var;
        $this->regexp = $regexp;
    }

    public function invoke(FunctionVariable $fun) {
        return InvokeRule($this, $fun);
    }

    /**
     * @inheritdoc
     */
    public function explain($text) {
        $r = new ContainTextRule($this->mode(), $this->var, $this->regexp);
        $r->setExplanation($text);
        return $r;
    }
}

