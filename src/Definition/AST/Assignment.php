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
 * An assignment of one variable to some definition.
 */
class Assignment extends Line {
    /**
     * @var Name
     */
    protected $name;

    /**
     * @var Definition
     */
    protected  $definition;

    public function __construct(Name $name, Definition $definition) {
        $this->name = $name;
        $this->definition = $definition;
    }

    /**
     * @return Name
     */
    public function name() {
        return $this->name;
    }

    /**
     * @return Definition
     */
    public function definition() {
        return $this->definition;
    }
}
