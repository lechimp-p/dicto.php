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
 * Tokenizes a rules file.
 */
class Tokenizer implements \Iterator {
    /**
     * @var SymbolTable
     */
    protected $symbol_table;

    /**
     * @var mixed[]
     */
    protected $tokens;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var int
     */
    protected $parsing_position;

    /**
     * @var bool
     */
    protected $is_end_token_added;

    public function __construct(SymbolTable $symbol_table, $source) {
        assert('is_string($source)');
        $this->symbol_table = $symbol_table;
        $this->tokens = array();
        $this->position = 0;
        $this->source = $source;
        $this->parsing_position = 0;
        $this->is_end_token_added = false;
    }

    // Methods from Iterator-interface

    /**
     * @return  array (Symbol,$matches)
     */
    public function current() {
        $this->maybe_parse_next_token();
        return $this->tokens[$this->position];
    }

    /**
     * @inheritdocs
     */
    public function key() {
        return $this->position;
    }

    /**
     * @inheritdocs
     */
    public function next() {
        $this->position++;
    }

    /**
     * @inheritdocs
     */
    public function rewind() {
        $this->position = 0;
    }

    /**
     * @inheritdocs
     */
    public function valid() {
        $this->maybe_parse_next_token();
        return count($this->tokens) > $this->position;
    }

    /**
     * Try to parse the next token if there are currently not enough tokens
     * in the tokens to get a token for the current position.
     *
     * @throws  ParserException if next token can not be parsed.
     */
    public function maybe_parse_next_token() {
        if (count($this->tokens) <= $this->position) {
            $this->parse_next_token();
        }
    }


    /**
     * Try to parse the next token from the source.
     *
     * @throws  ParserException if next token can not be parsed.
     */
    protected function parse_next_token() {
        if ($this->is_everything_parsed()) {
            if (!$this->is_end_token_added) {
                $this->tokens[] = array(new Symbol("", 0), array());
                $this->is_end_token_added = true;
            }
            return;
        }
        $tok = $this->get_next_token();

        foreach ($this->symbol_table->symbols() as $symbol) {
            $re = $symbol->regexp();
            $matches = array();
            if (preg_match("%^$re$%", $tok, $matches) == 1) {
                $this->tokens[] = array($symbol, $matches);
                return;
            }
        }

        throw new ParserException("Could not match \"$tok\".");
    }

    /**
     * Checkout if everything is parsed.
     *
     * @return  bool
     */
    protected function is_everything_parsed() {
        return empty(substr($this->source, $this->parsing_position));
    }

    /**
     * Get the next token from the source regarding to parsing_position.
     *
     * @throws  ParserException if next token can not be retreived.
     * @return  string
     */
    protected function get_next_token() {
        $tok_re = "%(.+?)($|\\s+)%";
        $flags = 0;
        $m = array();
        $r = preg_match($tok_re, $this->source, $m, $flags, $this->parsing_position);
        if ($r != 1) {
            throw new ParserException("Can not get next token.");
        }
        $this->parsing_position += strlen($m[0]);
        return $m[1];
    }
}
