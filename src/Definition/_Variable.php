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

class _Variable {
    /**
     * @param   _Variable $other
     * @throws  AssertionException when not ($other instanceof static::class)
     * @return  _Variable
     */
    public function _and(_Variable $other) {
        return new _And($this, $other);
    } 

    /**
     * @param   _Variable $other
     * @throws  AssertionException when not ($other instanceof static::class)
     * @return  _Variable
     */
    public function _except(_Variable $other) {
        return new _Except($this, $other);
    } 

    public function _with() {
        return new _With($this);
    } 

    public function cannot() {
        return new Cannot($this);
    }

    public function must() {
        return new Must($this);
    }
}

