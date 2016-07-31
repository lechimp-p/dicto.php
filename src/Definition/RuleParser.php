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
    const STRING_RE = "[\"]((\w|\s|([\\\\][\"])|([\\\\]n))+)[\"]";

    /**
     * @var Variable[]
     */
    protected $predefined_variables;

    public function __construct() {
        parent::__construct();
        $this->predefined_variables = array
            ( new V\Classes()
            , new V\Functions()
            , new V\Globals()
            , new V\Files()
            , new V\Methods()
            // TODO: Add some language constructs here...
            );

        // Assignment 
        $this->symbol(self::ASSIGNMENT_RE)
            ->null_denotation_is(function(array &$matches) {
                $this->fetch_next_token();
                $def = $this->variable_definition(0);
                $this->add_variable_definition($matches[1], $def);
                return null;
            });

        // Any
        $this->operator("{")
            ->null_denotation_is(function(array &$matches) {
                $arr = array();
                while(true) {
                    $arr[] = $this->variable_definition(0);
                    if ($this->is_current_token_operator("}")) {
                        $this->advance_operator("}");
                        return new V\Any($arr);
                    }
                    $this->advance_operator(",");
                }
            });
        $this->operator("}");
        $this->operator(",");

        // Except
        $this->operator("except", 10)
            ->left_denotation_is(function($left, array &$matches) {
                if (!($left instanceof V\Variable)) {
                    throw new ParserException
                        ("Expected a variable at the left of except.");
                }
                $right = $this->variable_definition(10);
                return new V\Except($left, $right);
            });

        // Strings
        $this->literal(self::STRING_RE, function(array &$matches) {
                return $this->unescape_string($matches[1]);
            });

        // Names
        $this->literal("\w+", function (array &$matches) {
                return $this->get_variable($matches[0]);
            });

        $this->symbol("\n");
    }

    /**
     * @return  Ruleset
     */
    public function parse($source) {
        $this->variables = array();
        $this->rules = array();
        $this->add_predefined_variables();
        return parent::parse($source);
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
        $this->purge_predefined_variables($this->variables);
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
        if (!($left instanceof V\Variable)) {
            throw new ParserException("Expected variable.");
        }
        return $left;
    }

    protected function string($right_binding_power = 0) {
        if (!$this->is_current_token_matched_by(self::STRING_RE)) {
            throw new ParserException("Expected string.");
        }
        $t = $this->current_symbol();
        $m = $this->current_match();
        return $t->null_denotation($m);
    }

    protected function add_variable_definition($name, $def) {
        if (array_key_exists($name, $this->variables)) {
            throw new ParserException("Variable '$name' already defined.");
        }
        assert('$def instanceof Lechimp\Dicto\Variables\Variable');
        $this->variables[$name] = $def->withName($name);
    }

    protected function get_variable($name) {
        if (!array_key_exists($name, $this->variables)) {
            throw new ParserException("Unknown variable '$name'.");
        }
        return $this->variables[$name];
    }

    protected function unescape_string($str) {
        assert('is_string($str)');
        return  str_replace("\\\"", "\"",
                    str_replace("\\n", "\n",
                        $str));
    }

    protected function add_predefined_variables() {
        foreach ($this->predefined_variables as $predefined_var) {
            $this->add_variable_definition($predefined_var->name(), $predefined_var);
        }
    }

    protected function purge_predefined_variables(array &$variables) {
        foreach ($this->predefined_variables as $predefined_var) {
            unset($variables[$predefined_var->name()]);
        }
    }
}
