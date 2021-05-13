<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph\Predicate;

use Lechimp\Dicto\Graph\Predicate;
use Lechimp\Dicto\Graph\Entity;

/**
 * A predicate that is always true.
 */
class _False extends Predicate {
    /**
     * @inheritdocs
     */
    public function _compile() {
        return function() { return false; };
    }

    /**
     * @inheritdocs
     */
    public function compile_to_source(array &$custom_closures) {
        return "    \$value = false;\n";
    }

    /**
     * @inheritdocs
     */
    public function for_types(array $existing_types) {
        return [];
    }
}
