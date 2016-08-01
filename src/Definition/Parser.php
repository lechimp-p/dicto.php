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

/**
 * Baseclass for Parsers.
 */
abstract class Parser {
    /**
     * @var SymbolTable
     */
    protected $symbol_table;

    /**
     * @var Tokenizer|null
     */
    protected $tokenizer = null;

    /**
     * @var array   (Symbol, array $matches)
     */
    protected $token;

    public function __construct() {
        $this->symbol_table = $this->create_symbol_table();
    }

    /**
     * Parse the string according to this parser.
     *
     * @return mixed
     */
    public function parse($source) {
        try {
            $this->tokenizer = $this->create_tokenizer($source);
            $this->token = $this->tokenizer->current();
            return $this->root();
        }
        finally {
            $this->tokenizer = null;
            $this->token = null;
        }
    }

    /**
     * The root for the parse tree.
     *
     * @return  mixed
     */
    abstract protected function root();

    // Factory Methods

    /**
     * Build the Tokenizer.
     *
     * @return  Tokenizer
     */
    public function create_tokenizer($source) {
        assert('is_string($source)');
        return new Tokenizer($this->symbol_table, $source);
    }

    /**
     * Build the SymbolTable
     *
     * @return SymbolTable
     */
    public function create_symbol_table() {
        // TODO: When symbol, operator and stuff were moved to
        //       Symbol table, there could be an add_symbols method
        //       postprocessing the table instead of using this->symbol etc.
        return new SymbolTable();
    }

    // Helpers for defining the grammar.

    /**
     * Add a symbol to the symbol table.
     *
     * TODO: This most probably should go to symbol table.
     *
     * @param   string  $regexp
     * @param   int     $binding_power
     * @throws  \InvalidArgumentException if %$regexp% is not a regexp
     * @throws  \LogicException if there already is a symbol with that $regexp.
     * @return  Symbol
     */
    protected function symbol($regexp, $binding_power = 0) {
        return $this->symbol_table->add_symbol($regexp, $binding_power);
    }

    /**
     * Add an operator to the symbol table.
     *
     * TODO: This most probably should go to symbol table.
     *
     * Convenience, will split the given string and wrap each char in []
     * before passing it to symbol.
     *
     * @param   string  $op
     * @param   int     $binding_power
     * @throws  \InvalidArgumentException if %$regexp% is not a regexp
     * @throws  \LogicException if there already is a symbol with that $regexp.
     * @return  Symbol
     */
    protected function operator($op, $binding_power = 0) {
        $regexp = $this->operator_regexp($op);
        return $this->symbol($regexp, $binding_power);
    }

    /**
     * Add a literal to the symbol table, where the matches are
     * transformed using the $converter.
     *
     * TODO: This most probably should go to symbol table.
     *
     * @param   string      $regexp
     * @param   \Closure    $converter
     * @throws  \InvalidArgumentException if %$regexp% is not a regexp
     * @throws  \LogicException if there already is a symbol with that $regexp.
     * @return  Symbol
     */
    protected function literal($regexp, $converter) {
        return $this->symbol($regexp)
            ->null_denotation_is($converter);
    }

    // Helpers for actual parsing.

    /**
     * Set the current token to the next token from the tokenizer.
     *
     * @return  null
     */
    protected function fetch_next_token() {
        assert('is_array($this->token)');
        assert('$this->tokenizer !== null');
        $this->tokenizer->next();
        $this->token = $this->tokenizer->current();
    }

    /**
     * Get the current symbol.
     *
     * @return  Symbol
     */
    protected function current_symbol() {
        return $this->token[0];
    }

    /**
     * Get the current match.
     *
     * @return  string[] 
     */
    protected function current_match() {
        return $this->token[1];
    }

    /**
     * Advance the tokenizer to the next token if current token
     * was matched by the given regexp.
     *
     * @param   string  $regexp
     * @return  null
     */
    protected function advance($regexp) {
        assert('is_string($regexp)');
        assert('is_array($this->token)');
        assert('$this->tokenizer !== null');
        if (!$this->is_current_token_matched_by($regexp)) {
            throw new ParserException("Syntax Error: Expected '$regexp'");
        }
        $this->tokenizer->next();
        $this->token = $this->tokenizer->current();
    }

    /**
     * Advance the tokenizer to the next token if current token
     * was matched by the given operator.
     *
     * @param   string  $op
     * @return  null
     */
    protected function advance_operator($op) {
        $this->advance($this->operator_regexp($op));
    }

    /**
     * Is the end of the file reached?
     *
     * @return  bool
     */
    public function is_end_of_file_reached() {
        return $this->is_current_token_matched_by("");
    }

    /**
     * Check if the current token was matched by the given regexp.
     *
     * @param   string  $regexp
     * @return  bool
     */
    protected function is_current_token_matched_by($regexp) {
        assert('is_string($regexp)');
        return $this->token[0]->regexp() == $regexp;
    }

    /**
     * Check if the current token is the given operator.
     *
     * @param   string  $operator
     * @return  bool
     */
    protected function is_current_token_operator($operator) {
        return $this->is_current_token_matched_by($this->operator_regexp($operator));
    }

    // Internal Helpers
    /**
     * "abc" -> "[a][b][c]"
     *
     * @param   string  $op
     * @return  string
     */
    protected function operator_regexp($op) {
        assert('is_string($op)');
        $regexp = array();
        foreach (str_split($op, 1) as $c) {
            $regexp[] = "[$c]";
        }
        return implode("", $regexp);
    }
}
