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
use Lechimp\Dicto\Variables as Vars;
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

        return new ExistingVar($this->rt);
    }

    /**
     * Say that the variable means functions.
     *
     * @return  Functions
     */
    public function functions() {
        // This is the first valid definition of a variable.
        $this->rt->current_var_is(new Vars\Functions($this->rt->get_current_var_name()));

        return new ExistingVar($this->rt);
    }

    /**
     * Say that the variable means methods.
     *
     * @return  Methods 
     */
    public function methods() {
        // This is the first valid definition of a variable.
        $this->rt->current_var_is(new Vars\Methods($this->rt->get_current_var_name()));

        return new ExistingVar($this->rt);
    }

    /**
     * Say that the variable means a language construct.
     *
     * @return  LanguageConstruct 
     */
    public function language_construct($name) {
        // This is the first valid definition of a variable.
        $this->rt->current_var_is(new Vars\LanguageConstruct($this->rt->get_current_var_name(), $name));

        return new ExistingVar($this->rt);
    }

    /**
     * Say that the variable means globals.
     *
     * @return  Globals
     */
    public function globals() {
        // This is the first valid definition of a variable.
        $this->rt->current_var_is(new Vars\Globals($this->rt->get_current_var_name()));

        return new ExistingVar($this->rt);
    }

    /**
     * Say that the variable means files.
     *
     * @return  Files
     */
    public function files() {
        // This is the first valid definition of a variable.
        $this->rt->current_var_is(new Vars\Files($this->rt->get_current_var_name()));

        return new ExistingVar($this->rt);
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
