<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

/**
 * Some predicate over an entity.
 */
abstract class Predicate
{
    /**
     * Compile the predicate to a function on an entity.
     *
     * @return  \Closure    Entity -> bool
     */
    final public function compile()
    {
        $custom_closures = [];
        $source =
            "return function (\\Lechimp\\Dicto\\Graph\\Entity \$e) use (\$custom_closures) {\n" .
            "    \$value = null;\n" .
            $this->compile_to_source($custom_closures) .
            "    assert('!is_null(\$value)');\n" .
            "    return \$value;\n" .
            "};\n";
        $closure = eval($source);
        assert('$closure instanceof \Closure');

        return $closure;
    }

    /**
     * Compile the predicate to some php source code to be consumed by compile.
     *
     * @param   \Closure[]  &$custom_closures
     * @return  string
     */
    abstract public function compile_to_source(array &$custom_closures);

    /**
     * Get the entity-types that could be matched by this predicate.
     *
     * @param   string[]    $existing_types
     * @return  string[]
     */
    abstract public function for_types(array $existing_types);
}
