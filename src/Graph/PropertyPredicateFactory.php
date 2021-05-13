<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

use Lechimp\Dicto\Regexp;

/**
 * Create some predicate over a property.
 */
class PropertyPredicateFactory {
    /**
     * @var string
     */
    protected $name;

    public function __construct($name) {
        assert('is_string($name)');
        $this->name = $name;
    }

    /**
     * Is true when the property matches the given regex.
     *
     * @param   Regexp  $regex
     * @return  Predicate
     */
    public function _matches(Regexp $regex) {
        return new Predicate\_PropertyMatches($this->name, $regex);
    }

    /**
     * Is true when the property equals the given value.
     *
     * @param   string      $value 
     * @return  Predicate
     */
    public function _equals($value) {
        return new Predicate\_PropertyEquals($this->name, $value);
    }
}
