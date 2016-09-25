<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Graph\Node;

class Except extends Combinator {
    /**
     * @inheritdocs
     */
    public function id() {
        return "except";
    }

    /**
     * @inheritdocs
     */
    public function compile($negate = false) {
        $left_condition = $this->left()->compile($negate);
        $right_condition = $this->right()->compile($negate);

        // normal case: left_condition and not right_condition
        if (!$negate) {
            return function(Node $n) use ($left_condition, $right_condition) {
                return $left_condition($n)
                    && !$right_condition($n);
            };
        }
        // negated case: not (left_condition and not right_condition)
        //             = not left_condition or right_condition
        else {
            return function(Node $n) use ($left_condition, $right_condition) {
                return $left_condition($n)
                    || !$right_condition($n);
            };
        }
    }
}
