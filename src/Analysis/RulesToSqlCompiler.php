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
                        ($query, $rule->mode(), $rule->subject(), $rule->regexp());
        }
        if ($rule instanceof Def\Rules\DependOn) {
            return $this->compile_depends_on
                        ($query, $rule->mode(), $rule->subject(), $rule->dependency());
        }
        if ($rule instanceof Def\Rules\Invoke) {
            return $this->compile_invoke
                        ($query, $rule->mode(), $rule->subject(), $rule->invokes());
        }
        throw new \LogicException("Unknown rule class '".get_class($rule)."'");
    }

    protected function compile_contains_text(Query $query, $mode, Vars\Variable $var, $regexp) {
        $builder = $query->builder();
        return $builder
            ->select("id", "type", "name", "file", "start_line", "end_line", "source")
            ->from($query->entity_table())
            ->where
                ( "source REGEXP ?"
                )
            ->setParameter(0, $regexp)
            ->execute();
    }

    protected function compile_depends_on(Query $query, $mode, Vars\Variable $subject, Vars\Variable $dependency) {
        $builder = $query->builder();
        return $builder
            ->select("d.dependent_id", "d.dependency_id", "d.file", "d.line", "d.source_line")
            ->from($query->dependencies_table(), "d")
            ->innerJoin("d", $query->entity_table(), "e", "d.dependent_id = e.id")
            ->innerJoin("d", $query->reference_table(), "r", "d.dependency_id = r.id")
            ->where
                ( $this->compile_var($builder->expr(), "e", $subject)
                , $this->compile_var($builder->expr(), "r", $dependency)
                )
            ->execute();
    }

    protected function compile_invoke(Query $query, $mode, Vars\Variable $subject, Vars\Variable $invokee) {
        $builder = $query->builder();
        return $builder
            ->select("i.invoker_id", "i.invokee_id", "i.file", "i.line", "i.source_line")
            ->from($query->invocations_table(), "i")

            ->innerJoin("i", $query->entity_table(), "e", "i.invoker_id = e.id")
            ->innerJoin("i", $query->reference_table(), "r", "i.invokee_id = r.id")
            ->where
                ( $this->compile_var($builder->expr(), "e", $subject)
                , $this->compile_var($builder->expr(), "r", $invokee)
                )
            ->execute();
    }

    public function compile_var(ExpressionBuilder $b, $table_name, Vars\Variable $var) {
        if ($var instanceof Vars\AsWellAs) {
        }
        if ($var instanceof Vars\ButNot) {
        }
        if ($var instanceof Vars\Classes) {
            return $b->eq("$table_name.type", $b->literal(Consts::CLASS_ENTITY));
        }
        if ($var instanceof Vars\Everything) {
            return $b->eq($b->literal(1), $b->literal(1));
        }
        if ($var instanceof Vars\Files) {
            return $b->eq("$table_name.type", $b->literal(Consts::FILE_ENTITY));
        }
        if ($var instanceof Vars\Functions) {
            return $b->eq("$table_name.type", $b->literal(Consts::FUNCTION_ENTITY));
        }
        if ($var instanceof Vars\Globals) {
            return $b->eq("$table_name.type", $b->literal(Consts::GLOBAL_ENTITY));
        }
        if ($var instanceof Vars\LanguageConstruct) {
            return $b->andX
                ( $b->eq("$table_name.type", $b->literal(Consts::LANGUAGE_CONSTRUCT_ENTITY))
                , $b->eq("$table_name.name", $b->literal($var->construct_name()))
                );
        }
        if ($var instanceof Vars\Methods) {
            return $b->eq("$table_name.type", $b->literal(Consts::METHOD_ENTITY));
        }
        if ($var instanceof Vars\WithName) {
        }
        throw new \LogicException("Can't compile var-type '".get_class($var)."'");
    }
} 
