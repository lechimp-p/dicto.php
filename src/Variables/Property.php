<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Graph\Node;
use Lechimp\Dicto\Definition\ArgumentParser;

/**
 * Defines the property of some variable.
 */
abstract class Property {
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
    public function parse_as() {
        return "with ".$this->name();
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
     * TODO: Maybe negate can go away.
     *
     * @param   array       &$arguments
     * @param   bool        $negate
     * @return  \Closure    Node -> bool
     */
    abstract public function compile(array &$arguments, $negate = false);
}
