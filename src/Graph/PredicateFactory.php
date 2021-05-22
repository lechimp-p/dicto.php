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

/**
 * Factory for Predicates on Nodes.
 */
class PredicateFactory
{
    /**
     * A predicate that is always true.
     *
     * @return  Predicate
     */
    public function _true()
    {
        return new Predicate\_True();
    }

    /**
     * A predicate that is always false.
     *
     * @return  Predicate
     */
    public function _false()
    {
        return new Predicate\_False();
    }

    /**
     * Connect some predicates with AND.
     *
     * @param   Predicate[]                 $predicates
     * @throws  \InvalidArgumentException   if count($predicates) == 0
     * @return  Predicate
     */
    public function _and(array $predicates)
    {
        if (count($predicates) == 0) {
            throw new \InvalidArgumentException("Predicates for _and can not be empty.");
        }
        if (count($predicates) == 1) {
            return array_shift($predicates);
        }
        return new Predicate\_And($predicates);
    }

    /**
     * Connect some predicates with OR.
     *
     * @param   Predicate[]     $predicates
     * @return  Predicate
     */
    public function _or(array $predicates)
    {
        if (count($predicates) == 0) {
            throw new \InvalidArgumentException("Predicates for _or can not be empty.");
        }
        if (count($predicates) == 1) {
            return array_shift($predicates);
        }
        return new Predicate\_Or($predicates);
    }

    /**
     * A negated predicate.
     *
     * @param   Predicate       $predicate
     * @return  Predicate
     */
    public function _not(Predicate $predicate)
    {
        return new Predicate\_Not($predicate);
    }

    /**
     * Is true when the node has the given type.
     *
     * @param   string      $type
     * @return  Predicate
     */
    public function _type_is($type)
    {
        return new Predicate\_TypeIs($type);
    }

    /**
     * A predicate about some property.
     *
     * @param   string      $name
     * @return  PropertyPredicate
     */
    public function _property($name)
    {
        return new PropertyPredicateFactory($name);
    }

    /**
     * A custom predicate.
     *
     * @param   \Closure    $predicate Entity -> bool
     * @return  Predicate
     */
    public function _custom(\Closure $predicate)
    {
        return new Predicate\_Custom($predicate);
    }
}
