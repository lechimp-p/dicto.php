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
 * Some predicate over an entity.
 */
class Predicate {
    /**
     * Compile the predicate to a function on an entity.
     *
     * @return  \Closure    Entity -> bool
     */
    public function compile() {
        return function(Entity $e) { return true; };
    }
}
