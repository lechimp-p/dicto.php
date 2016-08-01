<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Rules;

use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Variables as Vars;
use Lechimp\Dicto\Indexer\ListenerRegistry;
use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Variables\Variable;
use Doctrine\DBAL\Driver\Statement;

/**
 * This is what every rule needs to define.
 */
abstract class Schema {
    /**
     * Get the name of the schema.
     *
     * @return  string
     */
    abstract public function name(); 

    /**
     * Fetch arguments for the Schema from a stream of tokens during parsing.
     *
     * @param   ArgumentParser  $parser
     * @return  array
     */
    abstract public function fetch_arguments(ArgumentParser $parser);

    /**
     * Check the arguments given in the fluid interface on using the schema.
     *
     * TODO: This should stop throwing exception and return a boolean instead.
     *       Probably it should also be renamed. Then it could just be use to
     *       assert argument correctness on construction of Rule.
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
     * Turn a query result into a violation. Could be used 
     *
     * @param   Rule    $rule
     * @param   array   $row
     * @return  Violation
     */
    public function to_violation(Rule $rule, array $row) {
        return new Violation
            ( $rule
            , $row["file"]
            , (int)$row["line"]
            , $row["source"]
            );
    }

    /**
     * Register listeners to the indexer that are required to detect information
     * for the rule.
     *
     * @param   ListenerRegistry $registry
     * @return  null
     */
    abstract public function register_listeners(ListenerRegistry $registry);
}
