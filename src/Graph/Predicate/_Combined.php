<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph\Predicate;

use Lechimp\Dicto\Graph\Predicate;

/**
 * A predicate that is some combination of other predicates.
 */
abstract class _Combined extends Predicate {
    /**
     * @var Predicate[]
     */
    protected $predicates;

    public function __construct(array $predicates) {
        $this->predicates = array_map(function(Predicate $p) {
            return $p;
        }, $predicates);
    }

    /**
     * @return  \Closure[]
     */
    protected function compiled_predicates() {
        return array_map(function($p) {
            return $p->compile();
        }, $this->predicates);
    }

    /**
     * @param   string[]
     * @return  string[][]
     */
    protected function for_types_of_predicates(array $existing_types) {
        return array_map(function($p) use ($existing_types) {
            return $p->for_types($existing_types);
        }, $this->predicates);
    }
}
