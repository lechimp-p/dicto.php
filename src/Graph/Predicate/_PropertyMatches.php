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

use Lechimp\Regexp\Regexp;
use Lechimp\Dicto\Graph\Predicate;
use Lechimp\Dicto\Graph\Entity;

/**
 * A predicate that is true if a property of the entity matches a regex.
 */
class _PropertyMatches extends Predicate
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Regexp
     */
    protected $regexp;

    public function __construct($name, Regexp $regexp)
    {
        assert('is_string($name)');
        $this->name = $name;
        $this->regexp = $regexp;
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
            return $this->regexp->match($e->property($name));
        };
    }

    /**
     * @inheritdocs
     */
    public function compile_to_source(array &$custom_closures)
    {
        $name = $this->name;
        // If we didn't do this, backslash would not be escaped enough when
        // eval'ing them.
        $regexp = '%^' . str_replace("\\", "\\\\", $this->regexp->raw()) . '$%';
        return
            "   \$value = \n" .
            "       \$e->has_property(\"$name\")\n" .
            "       && (preg_match(\"$regexp\", \$e->property(\"$name\")) == 1);\n";
    }

    /**
     * @inheritdocs
     */
    public function for_types(array $existing_types)
    {
        return $existing_types;
    }
}
