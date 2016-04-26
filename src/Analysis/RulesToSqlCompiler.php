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

    protected function compile_contains_text(Query $query, $mode, Vars\Variable $var) {
        return $query->builder()
            ->select("id", "type", "name", "file", "start_line", "end_line", "source")
            ->from($query->entity_table())
            ->execute();
    }

    protected function compile_depends_on(Query $query, $mode, Vars\Variable $subject, Vars\Variable $dependency) {
        return $query->builder()
            ->select("dependent_id", "dependency_id", "file", "line", "source_line")
            ->from($query->dependencies_table())
            ->execute();
    }

    protected function compile_invoke(Query $query, $mode, Vars\Variable $subject, Vars\Variable $invokee) {
        return $query->builder()
            ->select("invoker_id", "invokee_id", "file", "line", "source_line")
            ->from($query->invocations_table())
            ->execute();
    }
} 
