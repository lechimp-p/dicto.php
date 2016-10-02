<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Definition as Def;

abstract class Variable extends Def\Definition {
    // TODO: Use these in Graph/IndexDB.
    const CLASS_TYPE = "class";
    const INTERFACE_TYPE = "interface";
    const FILE_TYPE = "file";
    const GLOBAL_TYPE = "global";
    const FUNCTION_TYPE = "function";
    const METHOD_TYPE = "method";
    const LANGUAGE_CONSTRUCT_TYPE = "language construct";

    static public function is_type($t) {
        static $types = array
            ( "class"
            , "file"
            , "global"
            , "function"
            , "method"
            , "language construct"
            );
        return in_array($t, $types);
    }

    /**
     * @var string|null
     */
    private $name;

    public function __construct($name = null) {
        assert('is_string($name) || ($name === null)');
        $this->name = $name;
    }

    /**
     * @return  string|null
     */
    public function name() {
        return $this->name;
    }

    /**
     * @param   string  $name
     * @return  self
     */
    public function withName($name) {
        assert('is_string($name)');
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    /**
     * Get the meaning of the variable.
     *
     * In opposite to name, this gives insight in the structure of this variable.
     *
     * @return  string
     */
    abstract public function meaning();

    /**
     * Compile the variable to a condition on a graph node.
     *
     * @return  \Closure    Node -> bool
     */
    abstract public function compile();
}

