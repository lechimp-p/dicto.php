<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition\AST;

/**
 * A rule.
 */
class Rule extends Line {
    /**
     * @var Qualifier
     */
    protected $qualifier;

    /**
     * @var Definition
     */
    protected $definition;

    // A rule is just the qualification over the existence of entities
    // according to some definition.
    public function __construct(Qualifier $qualifier, Definition $definition) {
        $this->qualifier = $qualifier;
        $this->definition = $definition;
    }

    /**
     * @return  Qualifier 
     */
    public function qualifier() {
        return $this->qualifier;
    }

    /**
     * @return  Definition
     */
    public function definition() {
        return $this->definition;
    }
}

