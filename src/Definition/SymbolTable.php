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
 * The symbol table knows all symbols we could construct.
 */
class SymbolTable
{
    /**
     * @var Symbol[]
     */
    protected $symbols = array();

    /**
     * Generator over the symbols the SymbolTable knows.
     *
     * @return Generator
     */
    public function symbols()
    {
        foreach ($this->symbols as $symbol) {
            yield $symbol;
        }
    }

    /**
     * Add a symbol to the table.
     *
     * @param   string  $regexp
     * @param   int     $binding_power
     * @throws  \InvalidArgumentException if %$regexp% is not a regexp
     * @throws  \LogicException if there already is a symbol with that $regexp.
     * @return  Symbol
     */
    public function add_symbol($regexp, $binding_power = 0)
    {
        if (array_key_exists($regexp, $this->symbols)) {
            throw new \LogicException("Symbol for regexp $regexp already exists.");
        }
        $s = new Symbol($regexp, $binding_power);
        $this->symbols[$regexp] = $s;
        return $s;
    }

    // HELPERS that make defining symbols a little more concise.

    /**
     * Add a symbol to the symbol table.
     *
     * @param   string  $regexp
     * @param   int     $binding_power
     * @throws  \InvalidArgumentException if %$regexp% is not a regexp
     * @throws  \LogicException if there already is a symbol with that $regexp.
     * @return  Symbol
     */
    public function symbol($regexp, $binding_power = 0)
    {
        return $this->add_symbol($regexp, $binding_power);
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
    public function operator($op, $binding_power = 0)
    {
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
    public function literal($regexp, $converter)
    {
        return $this->symbol($regexp)
            ->null_denotation_is($converter);
    }

    /**
     * "abc" -> "[a][b][c]"
     *
     * Makes handling operators like "*" easier.
     *
     * @param   string  $op
     * @return  string
     */
    public function operator_regexp($op)
    {
        assert('is_string($op)');
        $regexp = array();
        foreach (str_split($op, 1) as $c) {
            $regexp[] = "[$c]";
        }
        return implode("", $regexp);
    }
}
