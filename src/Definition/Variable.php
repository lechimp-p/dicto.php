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

abstract class Variable extends Definition {
    /**
     * @param   Variable $other
     * @throws  AssertionException when not ($other instanceof static::class)
     * @return  Variable
     */
    public function _and(Variable $other) {
        return new AndVariable($this, $other);
    } 

    /**
     * @param   Variable $other
     * @throws  AssertionException when not ($other instanceof static::class)
     * @return  Variable
     */
    public function _except(Variable $other) {
        return new ExceptVariable($this, $other);
    } 

    public function _with() {
        return new WithFluid($this);
    } 

    public function cannot() {
        return new CannotFluid($this);
    }

    public function must() {
        return new MustFluid($this);
    }
}

