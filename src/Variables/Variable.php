<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Graph\Predicate;
use Lechimp\Dicto\Graph\PredicateFactory;

abstract class Variable extends Def\Definition {
    // TODO: Use these in Graph/IndexDB.
    const NAMESPACE_TYPE = "namespace";
    const CLASS_TYPE = "class";
    const INTERFACE_TYPE = "interface";
    const TRAIT_TYPE = "trait";
    const FILE_TYPE = "file";
    const GLOBAL_TYPE = "global";
    const FUNCTION_TYPE = "function";
    const METHOD_TYPE = "method";
    const LANGUAGE_CONSTRUCT_TYPE = "language construct";

    public static function is_type($t) {
        static $types = array
            ( "namespace"
            , "class"
            , "interface"
            , "trait"
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
     * Compile the variable to a predicate on a graph node.
     *
     * @param   PredicateFactory $f
     * @return  Predicate
     */
    abstract public function compile(PredicateFactory $f);
}

