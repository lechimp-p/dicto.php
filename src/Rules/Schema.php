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
use Lechimp\Dicto\Variables as Vars;
use Lechimp\Dicto\Indexer\ListenerRegistry;
use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Variables\Variable;
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
     * Get the name where _ is replace by space.
     *
     * @return string
     */
    public function printable_name() {
        return str_replace("_", " ", $this->name());
    }

    /**
     * Get the Fluid interface that should be returned on using the
     * schema.
     *
     * @param   Def\RT    $rt
     * @param   string                  $name
     * @param   string                  $mode
     * @param   array                   $arguments
     * @return  Def\Fluid\Base|null
     */
    abstract public function fluid_interface(Def\RT $rt, $name, $mode, array $arguments);

    /**
     * Check the arguments given in the fluid interface on using the schema.
     *
     * @param   array   $arguments
     * @throws  \InvalidArgumentException   if $arguments are not ok
     * @return  null
     */
    abstract public function check_arguments(array $arguments);

    /**
     * Get a pretty printed version of the rules.
     *
     * @param   Rule    $rule
     * @return  string
     */
    abstract public function pprint(Rule $rule);

    /**
     * Compile a given rule into an sql statement using a query interface.
     *
     * @param   Query       $query
     * @param   Rule        $rule
     * @return  Statement
     */
    abstract public function compile(Query $query, Rule $rule);

    /**
     * Turn a query result into a violation.
     *
     * @param   Rule    $rule
     * @param   array   $row
     * @return  Violation
     */
    abstract public function to_violation(Rule $rule, array $row);

    /**
     * Register listeners to the indexer that are required to detect information
     * for the rule.
     *
     * @param   ListenerRegistry $registry
     * @return  null
     */
    abstract public function register_listeners(ListenerRegistry $registry);
}
