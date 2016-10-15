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
 * A predicate that is always true.
 */
class _True extends Predicate {
    /**
     * @inheritdocs
     */
    public function compile() {
        return function(Entity $e) { return true; };
    }

    /**
     * @inheritdocs
     */
    public function for_types(array $existing_types) {
        return $existing_types;
    }
}
