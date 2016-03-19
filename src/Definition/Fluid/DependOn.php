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

class DependOn extends BaseWithNameAndMode {
    public function __call($name, $arguments) {
        if (count($arguments) != 0) {
            throw new \InvalidArgumentException(
                "No arguments are allowed for a reference to a variable.");
        }
        $this->rt->throw_on_missing_var($name);

        $left = $this->rt->get_var($this->name);
        $right = $this->rt->get_var($name);
        $this->rt->add_rule(
            new Def\DependOnRule($this->mode, $left, $right));
    }
}
