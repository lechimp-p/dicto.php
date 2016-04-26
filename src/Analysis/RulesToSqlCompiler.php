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
        return $query->builder()
            ->select("id", "type", "name", "file", "start_line", "end_line", "source")
            ->from($query->entity_table())
            ->execute();
    }
} 
