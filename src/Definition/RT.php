<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Definition;

use Lechimp\Dicto\Rules\Schema;
use Lechimp\Dicto\Rules\Invoke;
use Lechimp\Dicto\Rules\DependOn;
use Lechimp\Dicto\Rules\ContainText;
use Lechimp\Dicto\Rules\Rule;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Variables\Variable;

/**
 * Runtime for one rule definition. A rule definition starts with
 * Dicto::startDefinition() and ends with Dicto::endDefinition().
 * This class provides the functionality that is accessed via the
 * Dicto class during the definition.
 */
class RT {
    /**
     * @var array   $name => $definition
     */
    private $vars;

    /**
     * ToDo: I think, this is not necessary and current_var is sufficient.
     *
     * @var string|null
     */
    private $current_var_name;

    /**
     * @var Variable|null
     */
    private $current_var;

    /**
     * @var Rule[]
     */
    private $rules;

    /**
     * @var array|null
     */
    private $config;

    /**
     * @var array<string,Schema>
     */
    private $known_schemas;

    public function __construct() {
        $this->vars = array();
        $this->current_var_name = null;
        $this->current_var = null;
        $this->rules = array();
        $this->config = null;
        // TODO: This needs to go somewhere else and must be more dynamic.
        $d = new DependOn();
        $i = new Invoke();
        $c = new ContainText();
        // TODO: There need to be checks on the name then as well.
        $this->known_schemas = array
            ( $d->name() => $d
            , $i->name() => $i
            , $c->name() => $c
            );
    }

    /**
     * Set a configuration to be used with the rules.
     *
     * @param   array|null  $config
     * @return  null|array
     */
    public function configuration(array $config = null) {
        if ($config === null) {
            return $this->config;
        }

        if ($this->config !== null) {
            throw new \RuntimeException("Already set configuration.");
        }
        $this->config = $config;
    }

    /**
     * Get the rule set that was currently created.
     *
     * @return  Ruleset
     */
    public function ruleset() {
        $this->maybe_save_current_var();

        return new Ruleset($this->vars, $this->rules);
    }

    /**
     * Define a new variable or reference an already defined variable to define
     * a rule.
     *
     * @throws  \RuntimeException   if previous variable declaration was not finished
     * @return  NewVar|RuleVar
     */
    public function variable($name) {
        $this->maybe_save_current_var();

        if (!array_key_exists($name, $this->vars)) {
            $this->current_var_name = $name;
            return new Fluid\NewVar($this);
        }
        else {
            $this->throw_on_missing_var($name);
            return new Fluid\RuleVar($this, $name);
        }
    }

    /**
     * Define a only-rule.
     *
     * @return  Fluid\Only
     */
    public function only() {
        $this->maybe_save_current_var();

        return new Fluid\Only($this);
    }

    /**
     * Save the currently defined variable, if there is any.
     *
     * @throws  \RuntimeException   if previous variable declaration was not finished
     */
    protected function maybe_save_current_var() {
        if ($this->current_var_name !== null) {
            if ($this->current_var === null) {
                throw new \RuntimeException(
                        "The declaration of ".$this->current_var_name.
                        " was not finished.");
            }
            $this->vars[$this->current_var_name] = $this->current_var;
            $this->current_var_name = null;
            $this->current_var = null;
        }
    }

    /**
     * Get the name of the current var.
     *
     * @return  string
     */
    public function get_current_var_name() {
        assert('is_string($this->current_var_name)');
        return $this->current_var_name;
    }

    /**
     * Get the name of the current var.
     *
     * @return  Variables\Variable
     */
    public function get_current_var() {
        assert('$this->current_var !== null');
        return $this->current_var;
    }

    /**
     * Get an already defined variable.
     *
     * @param   string  $name
     * @return  Variables\Variable
     */
    public function get_var($name) {
        assert('array_key_exists($name, $this->vars)');
        return $this->vars[$name];
    }

    /**
     * Announce what the current variable is atm.
     *
     * @param   Variable  $var
     */
    public function current_var_is(Variable $var) {
        assert('$this->current_var_name !== null');
        $this->current_var = $var;
    }

    /**
     * Throws a RuntimeException on missing variable $var.
     */
    public function throw_on_missing_var($var) {
        if (!array_key_exists($var, $this->vars)) {
            throw new \RuntimeException("Missing variable $var");
        }
    }

    /**
     * Add a rule to the set.
     */
    public function add_rule(Rule $rule) {
        $this->rules[] = $rule;
    }

    /**
     * Try to get a rule schema by name.
     *
     * @param   string  $name
     * @return  Schema|null
     */
    public function get_schema($name) {
        assert('is_string($name)');
        if (array_key_exists($name, $this->known_schemas)) {
            return $this->known_schemas[$name];
        }
        return null;
    }
}
