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

use Lechimp\Dicto\Regexp;
use Lechimp\Dicto\Graph\Predicate;
use Lechimp\Dicto\Graph\Entity;

/**
 * A predicate that is true if a property is equal to a given value.
 */
class _PropertyEquals extends Predicate
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    public function __construct($name, $value)
    {
        assert('is_string($name)');
        assert('is_string($value)');
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @inheritdocs
     */
    public function _compile()
    {
        $name = $this->name;
        return function (Entity $e) use ($name) {
            if (!$e->has_property($name)) {
                return false;
            }
            return $this->name === $e->property($name);
        };
    }

    /**
     * @inheritdocs
     */
    public function compile_to_source(array &$custom_closures)
    {
        $name = $this->name;
        $value = $this->value;
        return
            "   \$value = \n" .
            "       \$e->has_property(\"$name\")\n" .
            "       && (\$e->property(\"$name\") === \"$value\");\n";
    }

    /**
     * @inheritdocs
     */
    public function for_types(array $existing_types)
    {
        return $existing_types;
    }
}
