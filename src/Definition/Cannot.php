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
class Cannot {
    /**
     * @var Variable
     */
    private $var;

    public function __construct(Variable $var) {
        $this->var = $var;
    }

    public function invoke(Variable $var) {
        return new Invoke(Rule::MODE_CANNOT, $this->var, $var);
    }

    public function depend_on(Variable $var) {
        return new DependOn(Rule::MODE_CANNOT, $this->var, $var);
    }

    public function contain_text($text) {
        return new ContainText(Rule::MODE_CANNOT, $this->var, $text);
    }
}

