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
use Lechimp\Dicto\Rules as R;

/**
 * Parser for Rulesets.
 */
class RuleParser extends Parser implements ArgumentParser {
    const ASSIGNMENT_RE = "(\w+)\s*=\s*";
    const STRING_RE = "[\"]((([\\\\][\"])|[^\"])+)[\"]";
    const RULE_MODE_RE = "must|can(not)?";

    /**
     * @var V\Variable[]
     */
    protected $predefined_variables;

    /**
     * @var R\Schema[]
     */
    protected $schemas;

    /**
     * @var R\Property[]
     */
    protected $properties;

    /**
     * @var V\Variable[]
     */
    protected $variables = array();

    /**
     * @var R\Rule[]
     */
    protected $rules = array();

    /**
     * TODO: make arrays passed by reference as they get copied anyway.
     *
     * @param   V\Variable[]    $predefined_variables
     * @param   R\Schema[]      $schemas
     * @param   V\Property[]    $properties
     */
    public function __construct( array $predefined_variables
                               , array $schemas
                               , array $properties) {
        $this->predefined_variables = array_map(function(V\Variable $v) {
            return $v;
        }, $predefined_variables);
        $this->schemas = array_map(function(R\Schema $s) {
            return $s;
        }, $schemas);
        $this->properties = array_map(function(V\Property $p) {
            return $p;
        }, $properties);
        parent::__construct();
    }

    // Definition of symbols in the parser

    /**
     * @inheritdocs
     */
    protected function add_symbols_to(SymbolTable $table) {
        $this->add_symbols_for_variables_to($table);

        $this->add_symbols_for_rules_to($table);

        // Strings
        $table->symbol(self::STRING_RE);

        // Assignment 
        $table->symbol(self::ASSIGNMENT_RE);

        // Names
        $table->literal("\w+", function (array &$matches) {
                return $this->get_variable($matches[0]);
            });

        // End of statement
        $table->symbol("\n");
    }

    /**
     * @param   SymbolTable
     * @return  null
     */
    protected function add_symbols_for_variables_to(SymbolTable $table) {
        // Any
        $table->operator("{")
            ->null_denotation_is(function() {
                $arr = array();
                while(true) {
                    $arr[] = $this->variable(0);
                    if ($this->is_current_token_operator("}")) {
                        $this->advance_operator("}");
                        return new V\Any($arr);
                    }
                    $this->advance_operator(",");
                }
            });
        $table->operator("}");
        $table->operator(",");

        // Except
        $table->symbol("except", 10)
            ->left_denotation_is(function($left, array &$matches) {
                if (!($left instanceof V\Variable)) {
                    throw new ParserException
                        ("Expected a variable at the left of except.");
                }
                $right = $this->variable(10);
                return new V\Except($left, $right);
            });

        $this->add_symbols_for_properties_to($table, $this->properties);
    }

    /**
     * @param   SymbolTable     $table
     * @param   V\Property[]    $properties
     * @return  null
     */
    protected function add_symbols_for_properties_to(SymbolTable $table, array &$properties) {
        foreach ($properties as $property) {
            $table->symbol($property->parse_as().":", 20)
                ->left_denotation_is(function($left) use ($property) {
                    if (!($left instanceof V\Variable)) {
                        throw new ParserException
                            ("Expected a variable at the left of \"with name:\".");
                    }
                    $this->is_start_of_rule_arguments = true;
                    $arguments = $property->fetch_arguments($this);
                    assert('is_array($arguments)');
                    return new V\WithProperty($left, $property, $arguments);
                });
        }
    }

    /**
     * @param   SymbolTable
     * @return  null
     */
    protected function add_symbols_for_rules_to(SymbolTable $table) {
        // Rules
        $table->symbol("only");
        $table->symbol(self::RULE_MODE_RE, 0)
            ->null_denotation_is(function (array &$matches) {
                if ($matches[0] == "can") {
                    return R\Rule::MODE_ONLY_CAN;
                }
                if ($matches[0] == "must") {
                    return R\Rule::MODE_MUST;
                }
                if ($matches[0] == "cannot") {
                    return R\Rule::MODE_CANNOT;
                }
                throw new \LogicException("Unexpected \"".$matches[0]."\".");
            });
        $this->add_symbols_for_schemas_to($table, $this->schemas);
    }

    /**
     * @param   SymbolTable     $table
     * @param   R\Schema[]    $schemas
     * @return  null
     */
    protected function add_symbols_for_schemas_to(SymbolTable $table, array &$schemas) {
        foreach ($schemas as $schema) {
            $table->symbol($schema->name())
                ->null_denotation_is(function(array &$_) use ($schema) {
                    return $schema;
                });
        }
    }

    // IMPLEMENTATION OF Parser

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
     * Parses the top level statements in the rules file.
     *
     * @return  Ruleset 
     */
    protected function root() {
        while (true) {
            // drop empty lines
            while ($this->is_current_token_matched_by("\n")) {
                $this->advance("\n");
            }
            if ($this->is_end_of_file_reached()) {
                break;
            }

            // A top level statments is either..
            // ..an assignment to a variable.
            if ($this->is_current_token_matched_by(self::ASSIGNMENT_RE)) {
                $this->variable_assignment();
            }
            // ..or a rule declaration
            else {
                $this->rule_declaration();
            }

            if ($this->is_end_of_file_reached()) {
                break;
            }
            $this->advance("\n");
        }
        $this->purge_predefined_variables();
        return new Ruleset($this->variables, $this->rules);
    }

    // EXPRESSION TYPES

    /**
     * Fetch a rule mode from the stream.
     *
     * @return mixed
     */
    protected function rule_mode() {
        $this->is_current_token_matched_by(self::RULE_MODE_RE);
        $t = $this->current_symbol();
        $m = $this->current_match();
        $this->fetch_next_token();
        $mode = $t->null_denotation($m);
        return $mode;
    }

    /**
     * Fetch a string from the stream.
     *
     * @return  string
     */
    protected function string() {
        if (!$this->is_current_token_matched_by(self::STRING_RE)) {
            throw new ParserException("Expected string.");
        }
        $m = $this->current_match();
        $this->fetch_next_token();
        return  str_replace("\\\"", "\"",
                    str_replace("\\n", "\n",
                        $m[1]));
    }

    /**
     * Fetch a variable from the stream.
     *
     * @return  V\Variable
     */
    protected function variable($right_binding_power = 0) {
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

    /**
     * Fetch a rule schema and its arguments from the stream.
     *
     * @return  array   (R\Schema, array)
     */
    protected function schema() {
        $t = $this->current_symbol();
        $m = $this->current_match();
        $this->fetch_next_token();
        $schema = $t->null_denotation($m);
        if (!($schema instanceof R\Schema)) {
            throw new ParserException("Expected name of a rule schema.");
        }
        return $schema;
    }

    // TOP LEVEL STATEMENTS

    /**
     * Process a variable assignment.
     *
     * @return  null
     */
    protected function variable_assignment() {
        $m = $this->current_match(); 
        $this->fetch_next_token();
        $def = $this->variable();
        $this->add_variable($m[1], $def);
    }

    /**
     * Process a rule declaration.
     *
     * @return  null
     */
    protected function rule_declaration() {
        if ($this->is_current_token_matched_by("only")) {
            $this->advance("only");
        }
        $var = $this->variable();
        $mode = $this->rule_mode();
        $schema = $this->schema();
        $this->is_start_of_rule_arguments = true;
        $arguments = $schema->fetch_arguments($this);
        assert('is_array($arguments)');
        $this->rules[] = new R\Rule($mode, $var, $schema, $arguments);
    }


    // HANDLING OF VARIABLES

    /**
     * Add a variable to the variables currently known.
     *
     * @param   string      $name
     * @param   V\Variable  $def
     * @return null
     */
    protected function add_variable($name, V\Variable $def) {
        assert('is_string($name)');
        if (array_key_exists($name, $this->variables)) {
            throw new ParserException("Variable '$name' already defined.");
        }
        assert('$def instanceof Lechimp\\Dicto\\Variables\\Variable');
        $this->variables[$name] = $def->withName($name);
    }

    /**
     * Get a predefined variable.
     *
     * @param   string  $name
     * @return  V\Variable
     */
    protected function get_variable($name) {
        if (!array_key_exists($name, $this->variables)) {
            throw new ParserException("Unknown variable '$name'.");
        }
        return $this->variables[$name];
    }

    /**
     * Add all predefined variables to the current set of variables.
     *
     * @return null
     */
    protected function add_predefined_variables() {
        foreach ($this->predefined_variables as $predefined_var) {
            $this->add_variable($predefined_var->name(), $predefined_var);
        }
    }

    /**
     * Purge all predefined variables from the current set of variables.
     *
     * @return null
     */
    protected function purge_predefined_variables() {
        foreach ($this->predefined_variables as $predefined_var) {
            unset($this->variables[$predefined_var->name()]);
        }
    }

    // IMPLEMENTATION OF ArgumentParser

    /**
     * @var bool
     */
    protected $is_start_of_rule_arguments = false;

    protected function maybe_fetch_argument_delimiter() {
        if (!$this->is_start_of_rule_arguments) {
            $this->advance_operator(",");
            $this->is_start_of_rule_arguments = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function fetch_string() {
        $this->maybe_fetch_argument_delimiter();
        return $this->string();
    }

    /**
     * @inheritdoc
     */
    public function fetch_variable() {
        $this->maybe_fetch_argument_delimiter();
        return $this->variable();
    }
}
