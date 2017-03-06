<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Graph\PredicateFactory;

/**
 * Variable matching any of the sub variables.
 */
class Any extends Variable {
    /**
     * @var Variable[]
     */
    protected $variables;

    public function __construct(array $variables, $name = null) {
        parent::__construct($name);
        $this->variables = array_map(function(Variable $v) { return $v; }, $variables);
    }

    /**
     * @inheritdocs
     */
    public function meaning() {
        $meanings = array_map(function($v) { return $v->meaning(); }, $this->variables);
        return "{".implode(", ", $meanings)."}";
    }

    /**
     * @inheritdocs
     */
    public function compile(PredicateFactory $f) {
        $predicates = array_map(function(Variable $v) use ($f) {
            return $v->compile($f);
        }, $this->variables);
        return $f->_or($predicates);
    }
}

