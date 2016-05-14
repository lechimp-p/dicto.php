<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Variables;
use Lechimp\Dicto\Definition as Def;


abstract class Variable extends Def\Definition {
    const CLASS_ENTITY = "class";
    const FILE_ENTITY = "file";
    const GLOBAL_ENTITY = "global";
    const FUNCTION_ENTITY = "function";
    const METHOD_ENTITY = "method";
    const LANGUAGE_CONSTRUCT_ENTITY = "language_construct";

    static public function is_type($t) {
        static $types = array
            ( "class"
            , "file"
            , "global"
            , "function"
            , "method"
            , "language_construct"
            );
        return in_array($t, $types);
    }

    /**
     * @var string
     */
    private $name;

    public function __construct($name) {
        assert('is_string($name)');
        $this->name = $name;
    }

    public function name() {
        return $this->name;
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

