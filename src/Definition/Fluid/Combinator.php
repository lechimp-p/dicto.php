<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition\Fluid;

use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Variables as Vars;

/**
 * Provides fluid interface to combinators. 
 */
class Combinator extends Base {
    /**
     * @var \Closure
     */
    protected $constructor;

    public function __construct(Def\RT $rt, \Closure $constructor) {
        parent::__construct($rt);
        $this->constructor = $constructor;
    }

    /**
     * Tell with wich variable the other variable should be combined.
     *
     * @throws  \InvalidArgumentException   if $arguments are passed
     * @throws  \RuntimeException           if $name is unknown are passed
     * @return  ExistingVar 
     */
    public function __call($name, $arguments) {
        if (count($arguments) != 0) {
            # ToDo: This is used in Dicto::__callstatic as well.
            throw new \InvalidArgumentException(
                "No arguments are allowed for the reference to a variable.");
        }

        $left = $this->rt->get_current_var();
        $right = $this->rt->get_var($name);
        if (!($left instanceof Vars\Variable)) {
            throw new \RuntimeException("Could not get current var from runtime.");
        }
        $ctor = $this->constructor;
        $this->rt->current_var_is(
            $ctor($this->rt->get_current_var_name(), $left, $right));

        return new ExistingVar($this->rt);
    }
}
