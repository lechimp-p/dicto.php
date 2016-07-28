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
class Parser {
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
            return $this->expression(0);
        }
        finally {
            $this->tokenizer = null;
            $this->token = null;
        }
    }

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
        return new SymbolTable();
    }

    // Helpers for defining the grammar.

    /**
     * Add a symbol to the symbol table.
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
        if ($this->token[0]->regexp() != $regexp) {
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
