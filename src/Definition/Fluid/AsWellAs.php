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
use Lechimp\Dicto\Definition\Variables as Vars;

/**
 * Provides fluid interface to as_well_as().
 */
class AsWellAs extends Base {
    /**
     * Say to mean this class as well as the other class defined before.
     */
    public function __call($name, $arguments) {
        if (count($arguments) != 0) {
            # ToDo: This is used in Dicto::__callstatic as well.
            throw new \InvalidArgumentException(
                "No arguments are allowed for the reference to a variable.");
        }

        $left = $this->rt->get_current_var();
        $right = $this->rt->get_var($name);
        if (!($left instanceof Vars\Variable)) {
            throw new \RuntimeException("Could not get current var from runtime.");
        }
        $this->rt->current_var_is(
            new Vars\AsWellAs($this->rt->get_current_var_name(), $left, $right));

        return new ExistingVar($this->rt);
    }
}
