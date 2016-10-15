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

use Lechimp\Dicto\Graph\Entity;
/**
 * A predicate that is true if all of its subpredicates are true.
 */
class _And extends _Combined {
    /**
     * @inheritdocs
     */
    public function compile() {
        $compiled = array_map(function($p) {
            return $p->compile();
        }, $this->predicates);

        return function(Entity $e) use ($compiled) { 
            foreach ($compiled as $predicate) {
                if (!$predicate($e)) {
                    return false;
                }
            }
            return true;
        };
    }

    /**
     * @inheritdocs
     */
    public function for_types($existing_types) {
        $tss = array_map(function($p) use ($existing_types) {
            return $p->for_types($existing_types);
        }, $this->predicates);

        return array_values(call_user_func_array("array_intersect", $tss));
    }
}
