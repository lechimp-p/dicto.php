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
 * The symbol table knows all symbols we could construct.
 */
class SymbolTable {
    /**
     * @var Symbol[]
     */
    protected $symbols = array();

    /**
     * Generator over the symbols the SymbolTable knows.
     *
     * @return Generator
     */
    public function symbols() {
        foreach($this->symbols as $symbol) {
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
    public function add_symbol($regexp, $binding_power) {
        if (array_key_exists($regexp, $this->symbols)) {
            throw new \LogicException("Symbol for regexp $regexp already exists.");
        }
        $s = new Symbol($regexp, $binding_power);
        $this->symbols[$regexp] = $s;
        return $s;
    }
}

