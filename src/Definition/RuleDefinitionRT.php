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

/**
 * Runtime for one rule definition. A rule definition starts with
 * Dicto::startDefinition() and ends with Dicto::endDefinition().
 * This class provides the functionality that is accessed via the
 * Dicto class during the definition.
 */
class RuleDefinitionRT {
    /**
     * @var array   $name => $definition
     */
    private $vars;

    /**
     * @var string|null
     */
    private $current_var;

    public function __construct() {
        $this->vars = array();
        $this->current_var = null;
    }

    /**
     * Get the rule set that was currently created.
     *
     * @return  Ruleset
     */
    public function ruleset() {
        return new Ruleset();
    }

    /**
     * Define a new variable or reference an already defined variable to define
     * a rule.
     *
     * @return  NewVar|RuleVar
     */
    public function variable($name) {
        $this->maybe_save_current_var();

        if (!array_key_exists($name, $this->vars)) {
            $this->current_var = $name;
            return new Fluid\NewVar($this, $name);
        }
        else {
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
     */
    public function maybe_save_current_var() {
        if ($this->current_var !== null) {
            $this->vars[$this->current_var] = "VARIABLE";
            $this->current_var = null;
        }
    }
}
