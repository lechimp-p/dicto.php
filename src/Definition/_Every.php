<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition;

/**
 * Provides fluid interface to _every.
 */
class _Every {
    public function _class() {
        return new _Class;
    }
    public function _function() {
        return new _Function;
    }
    public function _global() {
        return new _Global;
    }
    public function _buildin() {
        return new _Buildin;
    }
    public function _file() {
        return new _File;
    }
}
