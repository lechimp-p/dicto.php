<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition\Fluid;
use Lechimp\Dicto\Definition as Def;

class Means extends Base {
    /**
     * @var string
     */
    protected $name;

    public function __construct(Def\RuleDefinitionRT $rt, $name) {
        parent::__construct($rt);
        assert('is_string($name)');
        $this->name = $name;
    }

    public function classes() {
        return new Classes;
    }

    public function functions() {
        return new Functions;
    }

    public function buildins() {
        return new Buildins;
    }

    public function globals() {
        return new Globals;
    }

    public function files() {
        return new Files;
    }

    public function __call($name, $arguments) {
        return new ExistingVar;
    }
}
