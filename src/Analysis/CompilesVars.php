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
        // Since SQL does not have a statement for negating while expressions,
        // we need to negate the single conditions in the expression, which
        // most often is the equality operator here.
        if (!$negate) {
            $eq_op = function($l, $r) use ($b) {
                return $b->eq($l, $r);
            };
        }
        else {
            $eq_op = function($l, $r) use ($b) {
                return $b->neq($l, $r);
            };
        }

        // sugar:
        $compile = function($dir, Vars\Compound $var, $negate = false) use ($table_name) {
            return $this->compile_var($table_name, $var->$dir(), $negate);
        };

        // Pattern matching on variable type.

        if ($var instanceof Vars\AsWellAs) {
            // normal case: left_condition or right_condition
            if (!$negate) {
                return $b->orX
                    ( $compile("left", $var)
                    , $compile("right", $var)
                    );
            }
            // negated case: not (left_condition or right_condition)
            //             = not left_condition and not right_condition
            if ($negate) {
                return $b->andX
                    ( $compile("left", $var, true)
                    , $compile("right", $var, true)
                    );
            }
        }
        if ($var instanceof Vars\ButNot) {
            return $b->andX
                ( $compile("left", $var)
                , $compile("right", $var, true)
                );
        }
        if ($var instanceof Vars\WithName) {
            // normal case : left_condition AND regexp matches
            if (!$negate) {
                return $b->andX
                    ( $this->compile_var($table_name, $var->variable())
                    , "$table_name.name REGEXP ".$b->literal('^'.$var->regexp().'$')
                    );
            }
            // negated case: not (left_condition_left and regexp matches)
            //             = not left_condition and not regexp matches
            else {
                return $b->orX
                    ( $this->compile_var($table_name, $var->variable(), true)
                    , "$table_name.name NOT REGEXP ".$b->literal('^'.$var->regexp().'$')
                    );
            }
        }
        return $var->compile($b, $table_name, $negate);
    }
}
