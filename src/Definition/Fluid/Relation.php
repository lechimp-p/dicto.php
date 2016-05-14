<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Definition\Fluid;
use \Lechimp\Dicto\Definition as Def;
use \Lechimp\Dicto\Rules as Rules;

class Relation extends BaseWithNameAndMode {
    /**
     * @var R\Relation
     */
    protected $relation;

    public function __construct(Def\RuleDefinitionRT $rt, $name, $mode, Rules\Relation $relation) {
        parent::__construct($rt, $name, $mode);
        $this->relation = $relation;
    } 

    public function __call($name, $arguments) {
        if (count($arguments) != 0) {
            throw new \InvalidArgumentException(
                "No arguments are allowed for a reference to a variable.");
        }
        $this->rt->throw_on_missing_var($name);

        $left = $this->rt->get_var($this->name);
        $right = $this->rt->get_var($name);
        $this->rt->add_rule(
            new Rules\Rule($this->mode, $left, $this->relation, array($right)));
    }
}
