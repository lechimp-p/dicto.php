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

/**
 * A predicate that is true if any of its subpredicates are true.
 */
class _Or extends _Combined {
    /**
     * @inheritdocs
     */
    public function _compile() {
        $compiled = $this->compiled_predicates();

        return function(Entity $e) use ($compiled) { 
            foreach ($compiled as $predicate) {
                if ($predicate($e)) {
                    return true;
                }
            }
            return false;
        };
    }

    /**
     * @inheritdocs
     */
    public function compile_to_source(array &$custom_closures) {
        $code =
            "    while(true) {\n";
        foreach ($this->predicates as $predicate) {
            $code .=
                 $predicate->compile_to_source($custom_closures).
            "    if (\$value) break;\n";
        }
        return
            $code.
            "break;\n".
            "}\n";
    }

    /**
     * @inheritdocs
     */
    public function for_types(array $existing_types) {
        $tss = $this->for_types_of_predicates($existing_types);
        return array_values(array_unique(call_user_func_array("array_merge", $tss)));
    }
}
