<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition;

class Invoke extends Rule {
    /**
     * @var _Variable
     */
    private $left;

    /**
     * @var _Variable
     */
    private $right;

    public function __construct($mode, _Variable $left, _Variable $right) {
        parent::__construct($mode);
        $this->left = $left;
        $this->right = $right;
    }

    public function invoke(_Function $fun) {
        return Invoke($this, $fun);
    }
}

