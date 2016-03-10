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

/**
 * Provides fluid interface to cannot.
 */
class Must {
    /**
     * @var _Variable
     */
    private $var;

    public function __construct(_Variable $var) {
        $this->var = $var;
    }

    public function invoke(_Variable $var) {
        return new Invoke(Rule::MODE_MUST, $this->var, $var);
    }

    public function depend_on(_Variable $var) {
        return new DependOn(Rule::MODE_MUST, $this->var, $var);
    }

    public function contain_text($text) {
        return new ContainText(Rule::MODE_MUST, $this->var, $var);
    }
}

