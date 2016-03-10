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

class Variable {
    /**
     * @param   Variable $other
     * @throws  AssertionException when not ($other instanceof static::class)
     * @return  Variable 
     */
    public function _and(Variable $other) {
        return new _And($this, $other);
    } 

    /**
     * @param   Variable $other
     * @throws  AssertionException when not ($other instanceof static::class)
     * @return  Variable 
     */
    public function _except(Variable $other) {
        return new _Except($this, $other);
    } 

    public function _with() {
        return new _With($this);
    } 
}

