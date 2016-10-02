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
    private $symbol_table;

    /**
     * @var Tokenizer|null
     */
    protected $tokenizer = null;

    /**
     * @var array|null   (Symbol, array $matches)
     */
    protected $token = null;

    public function __construct() {
        $this->symbol_table = $this->create_symbol_table();
        $this->add_symbols_to($this->symbol_table);
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
        catch (ParserException $e) {
            list($l, $c) = $this->tokenizer->source_position();
            $e->setPosition($l, $c);
            throw $e;
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
    protected function create_symbol_table() {
        return new SymbolTable();
    }

    /**
     * @param   SymbolTable
     * @return  null
     */
    abstract protected function add_symbols_to(SymbolTable $table);

    // Helpers for defining the grammar.


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
            $match = $this->current_match()[0];
            throw new ParserException("Syntax Error: Expected '$regexp', found '$match'");
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
        $this->advance($this->symbol_table->operator_regexp($op));
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
        return $this->is_current_token_matched_by
            ($this->symbol_table->operator_regexp($operator));
    }
}
