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

use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Definition\ArgumentParser;
use Lechimp\Dicto\Indexer\ListenerRegistry;
use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\Analysis\Index;
use Lechimp\Dicto\Graph\Query;

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
     * Check if the given arguments are valid for the rule schema.
     *
     * @param   array   $arguments
     * @return  bool 
     */
    abstract public function arguments_are_valid(array &$arguments);

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
     * @param   Index       $index
     * @param   Rule        $rule
     * @return  Query[]
     */
    abstract public function compile(Index $index, Rule $rule);

    /**
     * Register listeners to the indexer that are required to detect information
     * for the rule.
     *
     * @param   ListenerRegistry $registry
     * @return  null
     */
    abstract public function register_listeners(ListenerRegistry $registry);
}
