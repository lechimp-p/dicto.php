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

use Lechimp\Dicto\Graph\Entity;
use Lechimp\Dicto\Graph\Predicate;

/**
 * A custom predicate on an entity.
 */
class _Custom extends Predicate
{
    /**
     * @var \Closure
     */
    protected $predicate;

    public function __construct(\Closure $predicate)
    {
        $this->predicate = $predicate;
    }

    /**
     * @inheritdocs
     */
    public function _compile()
    {
        return $this->predicate;
    }

    /**
     * @inheritdocs
     */
    public function compile_to_source(array &$custom_closures)
    {
        $num = count($custom_closures);
        $custom_closures[] = $this->predicate;
        return
            "    \$value = \$custom_closures[$num](\$e);\n";
    }

    /**
     * @inheritdocs
     */
    public function for_types(array $existing_types)
    {
        return $existing_types;
    }
}
