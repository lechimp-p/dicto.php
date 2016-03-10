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

    public function __construct($mode, _Variable $var, $text) {
        parent::__construct($mode);
        assert('is_string($text)');
        $this->var = $var;
    }

    public function invoke(_Function $fun) {
        return Invoke($this, $fun);
    }
}

