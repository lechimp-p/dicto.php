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

class ContainText extends Rule {
    /**
     * @var _Variable
     */
    private $var;

    /**
     * @var string
     */
    private $regexp;

    public function __construct($mode, _Variable $var, $regexp) {
        parent::__construct($mode);
        preg_match("%$regexp%", "");
        $this->var = $var;
        $this->regexp = $regexp;
    }

    public function invoke(_Function $fun) {
        return Invoke($this, $fun);
    }

    /**
     * @inheritdoc
     */
    public function explain($text) {
        $r = new ContainText($this->mode(), $this->var, $this->regexp);
        $r->setExplanation($text);
        return $r;
    }
}

