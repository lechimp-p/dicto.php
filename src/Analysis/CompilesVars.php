<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Analysis;

use Lechimp\Dicto\Variables as Vars;
use Lechimp\Dicto\Variables\Variable;

/**
 * Implementation for Query::compile_var.
 */
trait CompilesVars {
    /**
     * Get a builder to create queries.
     *
     * @return  QueryBuilder
     */
    abstract public function builder();

    /**
     * Compile a variable to an SQL statement over a named table.
     *
     * @param   string          $table_name
     * @param   Vars\Variable   $var
     * @param   bool            $negate
     * @return  string|CompositeExpression
     */ 
    public function compile_var($table_name, Vars\Variable $var, $negate = false) {
        $b = $this->builder()->expr();
        return $var->compile($b, $table_name, $negate);
    }
}
