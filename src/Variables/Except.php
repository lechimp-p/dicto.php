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
    public function compile() {
        $left_condition = $this->left()->compile();
        $right_condition = $this->right()->compile();

        return function(Node $n) use ($left_condition, $right_condition) {
            return $left_condition($n)
                && !$right_condition($n);
        };
    }
}
