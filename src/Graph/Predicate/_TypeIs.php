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
use Lechimp\Dicto\Graph\Entity;

/**
 * A predicate that is true if entity has a certain type.
 */
class _TypeIs extends Predicate {
    /**
     * @var string
     */
    protected $type;

    public function __construct($type) {
        assert('is_string($type)');
        $this->type = $type; 
    }
     
    /**
     * @inheritdocs
     */
    public function compile() {
        $type = $this->type;
        return function(Entity $e) use ($type) { 
            return $e->type() == $type; 
        };
    }

    /**
     * @inheritdocs
     */
    public function for_types(array $existing_types) {
        if (!in_array($this->type, $existing_types)) {
            return [];
        }
        return [$this->type];
    }
}
