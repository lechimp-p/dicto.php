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
class EveryFluid {
    public function _class() {
        return new ClassVariable;
    }
    public function _function() {
        return new FunctionVariable;
    }
    public function _global() {
        return new GlobalVariable;
    }
    public function _buildin() {
        return new BuildinVariable;
    }
    public function _file() {
        return new FileVariable;
    }
}
