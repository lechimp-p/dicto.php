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
    private $dependency;

    public function __construct($mode, Variable $left, Variable $dependency) {
        parent::__construct($mode, $left);
        $this->dependency = $dependency;
    }

    /**
     * @var Variable
     */
    public function dependency() {
        return $this->dependency;
    }

    public function invoke(Functions $fun) {
        return InvokeRule($this, $fun);
    }

    /**
     * @inheritdoc
     */
    public function explain($text) {
        $r = new DependOnRule($this->mode(), $this->subject(), $this->dependency);
        $r->setExplanation($text);
        return $r;
    }
}

