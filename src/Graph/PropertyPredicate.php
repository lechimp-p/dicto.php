<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

/**
 * Some predicate over a property.
 */
class PropertyPredicate {
    /**
     * Is true when the property matches the given regex.
     *
     * @param   string  $regex
     * @return  Predicate
     */
    public function _matches($regex) {
        return new Predicate();
    } 
}
