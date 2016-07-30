<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 * 
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition;

use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Variables as V;

/**
 * Parser for Rulesets.
 */
class RuleParser extends Parser {
    const ASSIGNMENT_RE = "(\w+)\s*=\s*";

    public function __construct() {
        parent::__construct();
        // Assignment 
        $this->symbol(self::ASSIGNMENT_RE)
            ->null_denotation_is(function(array &$matches) {
                $this->fetch_next_token();
                $def = $this->variable_definition(0);
                $this->add_variable_definition($matches[1], $def);
                return null;
            });
        // Known Names
        $this->literal("Classes", function (&$_) {
                return new V\Classes("Classes"); 
            });
        $this->literal("Functions", function (&$_) {
                return new V\Functions("Functions"); 
            });

        $this->symbol("\n");
    }

    /**
     * The root for the parse tree.
     *
     * @return  Ruleset 
     */
    protected function root() {
        // Empty file
        if ($this->is_end_of_file_reached()) {
            return new Ruleset(array(), array());
        }
        $this->variables = array();
        $this->rules = array();
        while (true) {
            $t = $this->current_symbol();
            $m = $this->current_match(); 
            if ($this->is_current_token_matched_by(self::ASSIGNMENT_RE)) {
                $t->null_denotation($m);
            }
            else {
                throw new ParserException
                    ("Unexpected '".$m[0]."', expected assignment.");
            }

            if ($this->is_end_of_file_reached()) {
                break;
            }
            $this->advance("\n");
        }
        return new Ruleset($this->variables, $this->rules);
    }

    protected function variable_definition($right_binding_power = 0) {
        $t = $this->current_symbol();
        $m = $this->current_match();
        $this->fetch_next_token();
        $left = $t->null_denotation($m);

        while ($right_binding_power < $this->token[0]->binding_power()) {
            $t = $this->current_symbol();
            $m = $this->current_match();
            $this->fetch_next_token();
            $left = $t->left_denotation($left, $m);
        }
        return $left;
    }

    protected function add_variable_definition($name, $def) {
        if (array_key_exists($name, $this->variables)) {
            throw new ParserException("Variable '$name' already defined.");
        }
        assert('$def instanceof Lechimp\Dicto\Variables\Variable');
        $this->variables[$name] = $def->withName($name);
    }
}
