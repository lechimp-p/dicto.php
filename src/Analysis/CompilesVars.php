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

        // Pattern matching on variable type.

        if ($var instanceof Vars\AsWellAs) {
            // normal case: left_condition or right_condition
            if (!$negate) {
                return $b->orX
                    ( $this->compile_var($table_name, $var->left())
                    , $this->compile_var($table_name, $var->right())
                    );
            }
            // negated case: not (left_condition or right_condition)
            //             = not left_condition and not right_condition
            if ($negate) {
                return $b->andX
                    ( $this->compile_var($table_name, $var->left(), true)
                    , $this->compile_var($table_name, $var->right(), true)
                    );
            }
        }
        if ($var instanceof Vars\ButNot) {
            return $b->andX
                ( $this->compile_var($table_name, $var->left())
                , $this->compile_var($table_name, $var->right(), true)
                );
        }
        if ($var instanceof Vars\Classes) {
            return $eq_op("$table_name.type", $b->literal(Variable::CLASS_TYPE));
        }
        if ($var instanceof Vars\Everything) {
            return $eq_op($b->literal(1), $b->literal(1));
        }
        if ($var instanceof Vars\Files) {
            return $eq_op("$table_name.type", $b->literal(Variable::FILE_TYPE));
        }
        if ($var instanceof Vars\Functions) {
            return $eq_op("$table_name.type", $b->literal(Variable::FUNCTION_TYPE));
        }
        if ($var instanceof Vars\Globals) {
            return $eq_op("$table_name.type", $b->literal(Variable::GLOBAL_TYPE));
        }
        if ($var instanceof Vars\LanguageConstruct) {
            // normal case : language construct and name matches
            if (!$negate) {
                return $b->andX
                    ( $eq_op("$table_name.type", $b->literal(Variable::LANGUAGE_CONSTRUCT_TYPE))
                    , $eq_op("$table_name.name", $b->literal($var->construct_name()))
                    );
            }
            // negated case: not (language construct and name matches)
            //             = not language construct or not name matches
            else {

                return $b->orX
                    ( $eq_op("$table_name.type", $b->literal(Variable::LANGUAGE_CONSTRUCT_TYPE))
                    , $eq_op("$table_name.name", $b->literal($var->construct_name()))
                    );
            }
        }
        if ($var instanceof Vars\Methods) {
            return $eq_op("$table_name.type", $b->literal(Variable::METHOD_TYPE));
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
        throw new \LogicException("Can't compile var-type '".get_class($var)."'");
    }

}
