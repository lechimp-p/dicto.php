<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition;

class AndVariable extends Variable {
    /**
     * @var Variable
     */
    private $left;

    /**
     * @var Variable
     */
    private $right;

    public function __construct(Variable $left, Variable $right) {
        if (get_class($left) !== get_class($right)) {
            throw new \InvalidArgumentException(
                get_class($left).
                " and ".
                get_class($right).
                " do not have the same class.");
        }
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * @return  Variable
     */
    public function left() {
        return $this->left;
    }

    /**
     * @return  Variable
     */
    public function right() {
        return $this->right;
    }

    /**
     * @inheritdoc
     */
    public function explain($text) {
        $v = new AndVariable($this->left, $this->right);
        $v->setExplanation($text);
        return $v;
    }
}
