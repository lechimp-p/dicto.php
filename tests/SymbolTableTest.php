<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Definition\Symbol;
use Lechimp\Dicto\Definition\SymbolTable;

class SymbolTableTest extends \PHPUnit\Framework\TestCase
{
    public function setUp() : void
    {
        $this->symbol_table = new SymbolTable();
    }

    public function test_null_table()
    {
        $symbols = iterator_to_array($this->symbol_table->symbols());
        $this->assertEmpty($symbols);
    }

    public function test_add_symbol()
    {
        $s = $this->symbol_table->add_symbol("foo", 10);
        $symbols = iterator_to_array($this->symbol_table->symbols());

        $expected = new Symbol("foo", 10);
        $this->assertEquals($expected, $s);
        $this->assertEquals(array($expected), $symbols);
    }

    public function test_no_double_symbol()
    {
        $this->symbol_table->add_symbol("foo", 10);

        try {
            $this->symbol_table->add_symbol("foo", 10);
            $this->assertFalse("This should not happen.");
        } catch (\LogicException $e) {
            $this->assertTrue(true);
        }
    }
}
