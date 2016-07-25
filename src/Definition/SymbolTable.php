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
     * Generator over the symbols the SymbolTable knows.
     *
     * The generated values are pairs of (regex, symbol constructor).
     *
     * @return Generator
     */
    public function symbols() {
    }
}

