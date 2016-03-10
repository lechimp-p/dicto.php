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

class _And extends _Variable {
    /**
     * @var _Variable
     */
    private $left;

    /**
     * @var _Variable
     */
    private $right;

    public function __construct(_Variable $left, _Variable $right) {
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
     * @inheritdoc
     */
    public function explain($text) {
        $v = new _And($this->left, $this->right);
        $v->setExplanation($text);
        return $v;
    }
}
