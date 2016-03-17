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
use Lechimp\Dicto\Definition\Variables as Vars;
use Lechimp\Dicto\Definition as Def;

/**
 * Provides fluid interface for means().
 */
class Means extends Base {
    /**
     * Say that the variable means classes.
     *
     * @return  Classes
     */
    public function classes() {
        // This is the first valid definition of a variable.
        $this->rt->current_var_is(new Vars\Classes($this->rt->get_current_var_name()));

        return new Classes($this->rt);
    }

    /**
     * Say that the variable means functions.
     *
     * @return  Functions
     */
    public function functions() {
        return new Functions($this->rt);
    }

    /**
     * Say that the variable means buildins.
     *
     * @return  Buildins
     */
    public function buildins() {
        return new Buildins($this->rt);
    }

    /**
     * Say that the variable means globals.
     *
     * @return  Globals
     */
    public function globals() {
        return new Globals($this->rt);
    }

    /**
     * Say that the variable means files.
     *
     * @return  Files
     */
    public function files() {
        return new Files($this->rt);
    }

    /**
     * Talk about an existing variable.
     *
     * @throws  \InvalidArgumentException   if $arguments are passed
     * @throws  \RuntimeException           if $name is unknown are passed
     * @return  ExistingVar
     */
    public function __call($name, $arguments) {
        if (count($arguments) != 0) {
            # ToDo: This is used in Dicto::__callstatic as well.
            throw new \InvalidArgumentException(
                "No arguments are allowed for the reference to a variable.");
        }
        $this->rt->throw_on_missing_var($name);

        $this->rt->current_var_is($this->rt->get_var($name));
        return new ExistingVar($this->rt);
    }
}
