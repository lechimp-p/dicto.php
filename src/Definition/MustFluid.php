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
class MustFluid {
    /**
     * @var Variable
     */
    private $var;

    public function __construct(Variable $var) {
        $this->var = $var;
    }

    public function invoke(Variable $var) {
        return new InvokeRule(Rule::MODE_MUST, $this->var, $var);
    }

    public function depend_on(Variable $var) {
        return new DependOnRule(Rule::MODE_MUST, $this->var, $var);
    }

    public function contain_text($text) {
        return new ContainTextRule(Rule::MODE_MUST, $this->var, $var);
    }
}

