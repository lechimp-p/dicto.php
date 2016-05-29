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

/**
 * Provides fluid interface to entities that were already defined before, at
 * least a bit.
 */
class ExistingVar extends Base {
    /**
     * Say that you want to state some properties of the variable.
     *
     * @return  With
     */
    public function with() {
        return new With($this->rt);
    }

    /**
     * Combine the existing variable with another variable.
     *
     * @throws  \InvalidArgumentException   if $arguments are passed
     * @throws  \RuntimeException           if $name is unknown are passed
     * @return  ExistingVar
     */
    public function __call($name, $arguments) {
        $combinator_constructor = $this->rt->get_combinator_constructor($name);
        if ($combinator_constructor !== null) {
            return new Combinator($this->rt, $combinator_constructor);
        }

        throw new \InvalidArgumentException("Unknown combinator '$name'.");
    }


    /**
     * Explain something about the variable.
     *
     * @return  null
     */
    public function explain($explanation) {
        $var = $this->rt->get_current_var();
        $this->rt->current_var_is($var->explain($explanation));
    }
}
