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

class DependOnRule extends Rule {
    /**
     * @var Variable
     */
    private $left;

    /**
     * @var Variable
     */
    private $right;

    public function __construct($mode, Variable $left, Variable $right) {
        parent::__construct($mode);
        $this->left = $left;
        $this->right = $right;
    }

    public function invoke(FunctionVariable $fun) {
        return InvokeRule($this, $fun);
    }

    /**
     * @inheritdoc
     */
    public function explain($text) {
        $r = new DependOnRule($this->mode(), $this->left, $this->right);
        $r->setExplanation($text);
        return $r;
    }
}

