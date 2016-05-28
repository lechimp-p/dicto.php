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
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;


abstract class Variable extends Def\Definition {
    const CLASS_TYPE = "class";
    const FILE_TYPE = "file";
    const GLOBAL_TYPE = "global";
    const FUNCTION_TYPE = "function";
    const METHOD_TYPE = "method";
    const LANGUAGE_CONSTRUCT_TYPE = "language_construct";

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

    /**
     * @return  string
     */
    public function name() {
        return $this->name;
    }

    /**
     * Compile the variable to an sql expression.
     *
     * @param   ExpressionBuilder   $builder
     * @param   string              $table_name
     * @param   bool                $negate
     * @return  string|CompositeExpression
     */
    abstract public function compile(ExpressionBuilder $builder, $table_name, $negate = false);
}

