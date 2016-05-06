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

use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Definition\Variables as Vars;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

/**
 * This is a compiler that uses Doctrines QueryBuilder to transform rules to
 * SQL statements.
 */
class RulesToSqlCompiler {
    /**
     * Build the query for the rules using the provided query builder.
     *
     * @param   Query           $query
     * @param   Def\Rules\Rule  $rule
     * @return  Statement 
     */
    public function compile(Query $query, Def\Rules\Rule $rule) {
        if ($rule instanceof Def\Rules\ContainText) {
            return $this->compile_contains_text
                        ($query, $rule->mode(), $rule->checked_on(), $rule->regexp());
        }
        if ($rule instanceof Def\Rules\DependOn) {
            return $this->compile_depends_on
                        ($query, $rule->mode(), $rule->checked_on(), $rule->dependency());
        }
        if ($rule instanceof Def\Rules\Invoke) {
            return $this->compile_invoke
                        ($query, $rule->mode(), $rule->checked_on(), $rule->invokes());
        }
        throw new \LogicException("Unknown rule class '".get_class($rule)."'");
    }

    protected function compile_contains_text(Query $query, $mode, Vars\Variable $checked_on, $regexp) {
        $builder = $query->builder();
        if ($mode == Def\Rules\Rule::MODE_CANNOT || $mode == Def\Rules\Rule::MODE_ONLY_CAN) {
            return $builder
                ->select("id", "type", "name", "file", "start_line", "end_line", "source")
                ->from($query->entity_table())
                ->where
                    ( $this->compile_var($builder->expr(), $query->entity_table(), $checked_on)
                    , "source REGEXP ?"
                    )
                ->setParameter(0, $regexp)
                ->execute();
        }
        if ($mode == Def\Rules\Rule::MODE_MUST) {
            return $builder
                ->select("id", "type", "name", "file", "start_line", "end_line", "source")
                ->from($query->entity_table())
                ->where
                    ( $this->compile_var($builder->expr(), $query->entity_table(), $checked_on)
                    , "source NOT REGEXP ?"
                    )
                ->setParameter(0, $regexp)
                ->execute();
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }

    protected function compile_depends_on(Query $query, $mode, Vars\Variable $checked_on, Vars\Variable $dependency) {
        $builder = $query->builder();
        $b = $builder->expr();
        if ($mode == Def\Rules\Rule::MODE_CANNOT || $mode == Def\Rules\Rule::MODE_ONLY_CAN) {
            return $builder
                ->select("d.dependent_id", "d.dependency_id", "d.file", "d.line", "d.source_line")
                ->from($query->dependencies_table(), "d")
                ->innerJoin("d", $query->entity_table(), "e", "d.dependent_id = e.id")
                ->innerJoin("d", $query->reference_table(), "r", "d.dependency_id = r.id")
                ->where
                    ( $this->compile_var($b, "e", $checked_on)
                    , $this->compile_var($b, "r", $dependency)
                    )
                ->execute();
        }
        if ($mode == Def\Rules\Rule::MODE_MUST) {
            return $builder
                ->select("e.id")
                ->from($query->entity_table(), "e")
                ->leftJoin("e", $query->dependencies_table(), "d", "d.dependent_id = e.id")
                ->leftJoin
                    ("d", $query->reference_table(), "r"
                    , $b->andX
                        ( $b->eq("d.dependency_id", "r.id")
                        , $this->compile_var($b, "r", $dependency)
                        )
                    )
                ->where
                    ( $this->compile_var($b, "e", $checked_on)
                    , $b->isNull("r.id")
                    )
                ->execute();
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }

    protected function compile_invoke(Query $query, $mode, Vars\Variable $checked_on, Vars\Variable $invokee) {
        $builder = $query->builder();
        $b = $builder->expr();
        if ($mode == Def\Rules\Rule::MODE_CANNOT || $mode == Def\Rules\Rule::MODE_ONLY_CAN) {
            return $builder
                ->select("i.invoker_id", "i.invokee_id", "i.file", "i.line", "i.source_line")
                ->from($query->invocations_table(), "i")

                ->innerJoin("i", $query->entity_table(), "e", "i.invoker_id = e.id")
                ->innerJoin("i", $query->reference_table(), "r", "i.invokee_id = r.id")
                ->where
                    ( $this->compile_var($b, "e", $checked_on)
                    , $this->compile_var($b, "r", $invokee)
                    )
                ->execute();
        }
        if ($mode == Def\Rules\Rule::MODE_MUST) {
            return $builder
                ->select("e.id")
                ->from($query->entity_table(), "e")
                ->leftJoin("e", $query->invocations_table(), "i", "i.invoker_id = e.id")
                ->leftJoin
                    ( "i", $query->reference_table(), "r"
                    , $b->andX
                        ( $b->eq("i.invokee_id", "r.id")
                        , $this->compile_var($b, "r", $invokee)
                        )
                    )
                ->where
                    ( $this->compile_var($b, "e", $checked_on)
                    , $b->isNull("r.id")
                    )
                ->execute();
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }

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
