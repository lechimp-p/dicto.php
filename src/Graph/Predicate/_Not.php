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
 * A predicate that negates another predicate;
 */
class _Not extends _Combined {
    /**
     * @var Predicate
     */
    protected $predicate;

    public function __construct(Predicate $predicate) {
        $this->predicate = $predicate;
    }

    /**
     * @inheritdocs
     */
    public function _compile() {
        $compiled = $this->predicate->compile();
        return function(Entity $e) use ($compiled) { 
            return !$compiled($e);
        };
    }

    /**
     * @inheritdocs
     */
    public function compile_to_source(array &$custom_closures) {
        return
            $this->predicate->compile_to_source($custom_closures).
            "    \$value = !\$value;\n";
    }

    /**
     * @inheritdocs
     */
    public function for_types(array $existing_types) {
        // Can't really know what is in predicate, so this could match
        // all types.
        return $existing_types;
    }
}
