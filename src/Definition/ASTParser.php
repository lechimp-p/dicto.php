<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition;

/**
 * Parser for Rulesets.
 *
 * The grammar looks like this:
 *
 * explanation = '/ ** ... * /'
 * comment = '//..' | '/ * ... * /'
 * assignment = name "=" def
 * string = '"..."'
 * atom = [a-z ]...
 * name = [A-Z] [a-z]...
 * def = name | "{" def,... "}" | def property | def "except" def
 * property = atom ((name|string|atom)...)?
 * statement = def qualifier rule
 * rule = atom ((name|string|atom)...)?
 */
class ASTParser extends Parser
{
    const EXPLANATION_RE = "[/][*][*](([^*]|([*][^/]))*)[*][/]";
    const SINGLE_LINE_COMMENT_RE = "[/][/]([^\n]*)";
    const MULTI_LINE_COMMENT_RE = "[/][*](([^*]|([*][^/]))*)[*][/]";
    const RULE_MODE_RE = "must|can(not)?";
    const STRING_RE = "[\"]((([\\\\][\"])|[^\"])+)[\"]";
    const NAME_RE = "[A-Z][A-Za-z_]*";
    const ATOM_RE = "[a-z ]*";
    const ASSIGNMENT_RE = "(" . self::NAME_RE . ")\s*=\s*";
    const ATOM_HEAD_RE = "(" . self::ATOM_RE . ")\s*:\s*";

    /**
     * @var AST\Factory
     */
    protected $ast_factory;

    public function __construct(AST\Factory $ast_factory)
    {
        $this->ast_factory = $ast_factory;
        parent::__construct();
    }

    // Definition of symbols in the parser

    /**
     * @inheritdocs
     */
    protected function add_symbols_to(SymbolTable $table)
    {
        $this->add_symbols_for_comments($table);

        $this->add_symbols_for_variables_to($table);

        $this->add_symbols_for_rules_to($table);

        // Assignment
        $table->symbol(self::ASSIGNMENT_RE);

        // Strings
        $table->symbol(self::STRING_RE);

        // Names
        $table->literal(self::NAME_RE, function (array &$matches) {
            return $this->ast_factory->name($matches[0]);
        });

        // Head of a property or rule
        $table->symbol(self::ATOM_HEAD_RE, 20)
            ->left_denotation_is(function ($left, &$matches) {
                if (!($left instanceof AST\Definition)) {
                    throw new ParserException("Expected a variable at the left of \"{$matches[0]}\".");
                }
                $id = $this->ast_factory->atom($matches[1]);
                $arguments = $this->arguments();
                return $this->ast_factory->property($left, $id, $arguments);
            })
            ->null_denotation_is(function (&$matches) {
                return $this->ast_factory->atom($matches[1]);
            });



        // End of statement
        $table->symbol("\n");
    }

    /**
     * @param   SymbolTable
     * @return  null
     */
    protected function add_symbols_for_comments(SymbolTable $table)
    {
        $table->symbol(self::EXPLANATION_RE);
        $table->symbol(self::SINGLE_LINE_COMMENT_RE);
        $table->symbol(self::MULTI_LINE_COMMENT_RE);
    }

    /**
     * @param   SymbolTable
     * @return  null
     */
    protected function add_symbols_for_variables_to(SymbolTable $table)
    {
        // Any
        $table->operator("{")
            ->null_denotation_is(function () {
                $arr = array();
                while (true) {
                    $arr[] = $this->variable(0);
                    if ($this->is_current_token_operator("}")) {
                        $this->advance_operator("}");
                        return $this->ast_factory->any($arr);
                    }
                    $this->advance_operator(",");
                }
            });
        $table->operator("}");
        $table->operator(",");

        // Except
        $table->symbol("except", 10)
            ->left_denotation_is(function ($left) {
                if (!($left instanceof AST\Definition)) {
                    throw new ParserException("Expected a variable at the left of except.");
                }
                $right = $this->variable(10);
                return $this->ast_factory->except($left, $right);
            });
    }

    /**
     * @param   SymbolTable
     * @return  null
     */
    protected function add_symbols_for_rules_to(SymbolTable $table)
    {
        // Rules
        $table->symbol("only");
        $table->symbol(self::RULE_MODE_RE, 0)
            ->null_denotation_is(function (array &$matches) {
                if ($matches[0] == "can") {
                    return $this->ast_factory->only_X_can();
                }
                if ($matches[0] == "must") {
                    return $this->ast_factory->must();
                }
                if ($matches[0] == "cannot") {
                    return $this->ast_factory->cannot();
                }
                throw new \LogicException("Unexpected \"" . $matches[0] . "\".");
            });
    }

    // IMPLEMENTATION OF Parser

    /**
     * @return  Ruleset
     */
    public function parse($source)
    {
        $this->variables = array();
        $this->rules = array();
        return parent::parse($source);
    }

    /**
     * Root expression for the parser is some whitespace or comment where a
     * top level statement is in the middle.
     *
     * @return  Ruleset
     */
    protected function root()
    {
        $lines = [];
        while (true) {
            // drop empty lines
            while ($tok = $this->is_current_token_to_be_dropped()) {
                $this->advance($tok);
            }
            if ($this->is_end_of_file_reached()) {
                break;
            }

            $lines[] = $this->top_level_statement();
        }
        return $this->ast_factory->root($lines);
    }

    /**
     * Parses the top level statements in the rules file.
     *
     * @return  null
     */
    public function top_level_statement()
    {
        // A top level statements is either..
        // ..an explanation
        if ($this->is_current_token_matched_by(self::EXPLANATION_RE)) {
            $m = $this->current_match();
            $this->advance(self::EXPLANATION_RE);
            return $this->ast_factory->explanation($this->trim_explanation($m[1]));
        }
        // ..an assignment to a variable.
        elseif ($this->is_current_token_matched_by(self::ASSIGNMENT_RE)) {
            return $this->variable_assignment();
        }
        // ..or a rule declaration
        else {
            return $this->rule_declaration();
        }
    }

    /**
     * Returns currently matched whitespace or comment token if there is any.
     *
     * @return string|null
     */
    public function is_current_token_to_be_dropped()
    {
        if ($this->is_current_token_matched_by("\n")) {
            return "\n";
        }
        if ($this->is_current_token_matched_by(self::SINGLE_LINE_COMMENT_RE)) {
            return self::SINGLE_LINE_COMMENT_RE;
        }
        if ($this->is_current_token_matched_by(self::MULTI_LINE_COMMENT_RE)) {
            return self::MULTI_LINE_COMMENT_RE;
        }
        return null;
    }

    /**
     * @param   string
     * @return  string
     */
    protected function trim_explanation($content)
    {
        return trim(
            preg_replace("%\s*\n\s*([*]\s*)?%s", "\n", $content)
        );
    }

    /**
     * Fetch a rule mode from the stream.
     *
     * @return mixed
     */
    protected function rule_mode()
    {
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
    protected function string()
    {
        $m = $this->current_match();
        $this->fetch_next_token();
        return $this->ast_factory->string_value(str_replace(
                "\\\"",
                "\"",
                str_replace(
                    "\\n",
                    "\n",
                    $m[1]
                )
            ));
    }

    /**
     * Fetch a variable from the stream.
     *
     * @return  V\Variable
     */
    protected function variable($right_binding_power = 0)
    {
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

        if (!($left instanceof AST\Definition)) {
            throw new ParserException("Expected variable.");
        }

        return $left;
    }

    /**
     * Fetch some arguments from the stream.
     *
     * @return  array   of atoms, variables or strings
     */
    protected function arguments()
    {
        $args = [];
        while (true) {
            // An argument is either
            // ..a string
            if ($this->is_current_token_matched_by(self::STRING_RE)) {
                $m = $this->current_match();
                $this->fetch_next_token();
                $args[] = $this->ast_factory->string_value(str_replace(
                        "\\\"",
                        "\"",
                        str_replace(
                            "\\n",
                            "\n",
                            $m[1]
                        )
                    ));
            }
            // ..a variable
            // TODO: this won't do with {..}
            if ($this->is_current_token_matched_by(self::NAME_RE)) {
                $args[] = $this->variable(0);
            } else {
                break;
            }
        }
        return $args;
    }

    /**
     * Fetch a rule schema and its arguments from the stream.
     *
     * @return  array   (R\Schema, array)
     */
    protected function schema()
    {
        $t = $this->current_symbol();
        $m = $this->current_match();
        $this->fetch_next_token();
        $id = $t->null_denotation($m);
        if (!($id instanceof AST\Atom)) {
            throw new ParserException("Expected name of a rule schema.");
        }
        return $id;
    }

    // TOP LEVEL STATEMENTS

    /**
     * Process a variable assignment.
     *
     * @return  null
     */
    protected function variable_assignment()
    {
        $m = $this->current_match();
        $name = $this->ast_factory->name($m[1]);
        $this->fetch_next_token();
        $def = $this->variable();
        return $this->ast_factory->assignment($name, $def);
    }

    /**
     * Process a rule declaration.
     *
     * @return  null
     */
    protected function rule_declaration()
    {
        if ($this->is_current_token_matched_by("only")) {
            $this->advance("only");
        }
        $var = $this->variable();
        $mode = $this->rule_mode();
        $schema = $this->schema();
        $arguments = $this->arguments();
        assert('is_array($arguments)');
        return $this->ast_factory->rule(
                $mode,
                $this->ast_factory->property(
                    $var,
                    $schema,
                    $arguments
                )
            );
    }
}
