<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Rules;

use \Lechimp\Dicto\Definition as Def;

/**
 * This is a rule that defines a relation between two entities
 * in the code.
 */
abstract class Relation extends Schema {
    /**
     * @inheritdoc
     */
    public function fluid_interface(Def\RuleDefinitionRT $rt, $name, $mode) {
        return new Def\Fluid\Relation($rt, $name, $mode, $this);
    }

    /**
     * @inheritdoc
     */
    public function pprint($rule) {
        assert('$rule instanceof \\Lechimp\\Dicto\\Definition\\Rules\\Relation');
        return str_replace("_", " ", $this->name())." ".$rule->right()->name();
    }
}
