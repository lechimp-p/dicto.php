<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Variables;

class AsWellAs extends Variable {
    /**
     * @var Variable
     */
    private $left;

    /**
     * @var Variable
     */
    private $right;

    public function __construct($name, Variable $left, Variable $right) {
        parent::__construct($name);
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
        $v = new AsWellAs($this->name(), $this->left, $this->right);
        $v->setExplanation($text);
        return $v;
    }
}
