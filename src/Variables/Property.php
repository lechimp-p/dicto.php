<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Graph\Predicate;
use Lechimp\Dicto\Graph\PredicateFactory;
use Lechimp\Dicto\Definition\ArgumentParser;

/**
 * Defines the property of some variable.
 */
abstract class Property
{
    /**
     * Name of the property.
     *
     * @return  string
     */
    abstract public function name();

    /**
     * How to match property during parsing.
     *
     * Defaults to "with $name"
     *
     * @return string
     */
    public function parse_as()
    {
        return "with " . $this->name();
    }

    /**
     * Fetch arguments for the Property from a stream of tokens during parsing.
     *
     * @param   ArgumentParser  $parser
     * @return  array
     */
    abstract public function fetch_arguments(ArgumentParser $parser);

    /**
     * Check if the given arguments are valid for the property.
     *
     * @param   array   $arguments
     * @return  bool
     */
    abstract public function arguments_are_valid(array &$arguments);

    /**
     * Compile the property to a condition on a graph node.
     *
     * @param   PredicateFactory $f
     * @param   array       &$arguments
     * @return  Predicate
     */
    abstract public function compile(PredicateFactory $f, array &$arguments);
}
