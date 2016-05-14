<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Rules;

use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Definition\Variables as Vars;
use Lechimp\Dicto\Indexer\ListenerRegistry;
use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Analysis\Consts;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

/**
 * This is what every rule needs to define.
 */
abstract class Schema {
    /**
     * Get the name of the relation.
     *
     * This must return a string without whitespaces.
     *
     * @return  string
     */
    abstract public function name(); 

    /**
     * Get the Fluid interface that should be returned on using the
     * schema.
     *
     * @param   Def\RuleDefinitionRT    $rt
     * @param   string                  $name
     * @param   string                  $mode
     * @return  Def\Fluid\Base
     */
    abstract public function fluid_interface(Def\RuleDefinitionRT $rt, $name, $mode);

    /**
     * Get a pretty printed version of the rules.
     *
     * // TODO: What is this, seriously.
     * @param   ?   $rule
     * @return  string
     */
    abstract public function pprint($rule);

    /**
     * Compile a given rule into an sql statement using a query interface.
     *
     * @param   Query           $query
     * // TODO: What is this, seriously.
     * @param   ?   $rule
     * @return  Statement
     */
    abstract public function compile(Query $query, $rule);

    /**
     * Register listeners to the indexer that are required to detect information
     * for the rule.
     *
     * @param   ListenerRegistry $registry
     * @return  null
     */
    abstract public function register_listeners(ListenerRegistry $registry);


    // TODO: This most propably could go to Query.
    protected function compile_var(ExpressionBuilder $b, $table_name, Vars\Variable $var, $negate = false) {
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
            // normal case: $left_condition or $right_condition
            if (!$negate) {
                return $b->orX
                    ( $this->compile_var($b, $table_name, $var->left())
                    , $this->compile_var($b, $table_name, $var->right())
                    );
            }
            // negated case: not ($left_condition or $right_condition)
            //             = not $left_condition and not $right_condition
            if ($negate) {
                return $b->andX
                    ( $this->compile_var($b, $table_name, $var->left(), true)
                    , $this->compile_var($b, $table_name, $var->right(), true)
                    );
            }
        }
        if ($var instanceof Vars\ButNot) {
            return $b->andX
                ( $this->compile_var($b, $table_name, $var->left())
                , $this->compile_var($b, $table_name, $var->right(), true)
                );
        }
        if ($var instanceof Vars\Classes) {
            return $eq_op("$table_name.type", $b->literal(Consts::CLASS_ENTITY));
        }
        if ($var instanceof Vars\Everything) {
            return $eq_op($b->literal(1), $b->literal(1));
        }
        if ($var instanceof Vars\Files) {
            return $eq_op("$table_name.type", $b->literal(Consts::FILE_ENTITY));
        }
        if ($var instanceof Vars\Functions) {
            return $eq_op("$table_name.type", $b->literal(Consts::FUNCTION_ENTITY));
        }
        if ($var instanceof Vars\Globals) {
            return $eq_op("$table_name.type", $b->literal(Consts::GLOBAL_ENTITY));
        }
        if ($var instanceof Vars\LanguageConstruct) {
            // normal case : language construct and name matches
            if (!$negate) {
                return $b->andX
                    ( $eq_op("$table_name.type", $b->literal(Consts::LANGUAGE_CONSTRUCT_ENTITY))
                    , $eq_op("$table_name.name", $b->literal($var->construct_name()))
                    );
            }
            // negated case: not (language construct and name matches)
            //             = not language construct or not name matches
            else {

                return $b->orX
                    ( $eq_op("$table_name.type", $b->literal(Consts::LANGUAGE_CONSTRUCT_ENTITY))
                    , $eq_op("$table_name.name", $b->literal($var->construct_name()))
                    );
            }
        }
        if ($var instanceof Vars\Methods) {
            return $eq_op("$table_name.type", $b->literal(Consts::METHOD_ENTITY));
        }
        if ($var instanceof Vars\WithName) {
            // normal case : $condition_left AND regexp matches
            if (!$negate) {
                return $b->andX
                    ( $this->compile_var($b, $table_name, $var->variable())
                    , "$table_name.name REGEXP ".$b->literal('^'.$var->regexp().'$')
                    );
            }
            // negated case: not ($condition_left AND regexp matches)
            //             = not $condition_left OR not regexp matches
            else {
                return $b->orX
                    ( $this->compile_var($b, $table_name, $var->variable(), true)
                    , "$table_name.name NOT REGEXP ".$b->literal('^'.$var->regexp().'$')
                    );
            }
        }
        throw new \LogicException("Can't compile var-type '".get_class($var)."'");
    }


}
