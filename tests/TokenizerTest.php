<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Definition\Tokenizer;
use Lechimp\Dicto\Definition\Symbol;
use Lechimp\Dicto\Definition\SymbolTable;
use Lechimp\Dicto\Definition\ParserException;

class SymbolTableMock extends SymbolTable {
    public $all_symbols = array();
    public function symbols() {
        foreach ($this->all_symbols as $symbol) {
            yield new Symbol($symbol, 0);
        }
    }
}

class TokenizerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->symbol_table = new SymbolTableMock();
    }

    protected function tokenizer($source) {
        return new Tokenizer($this->symbol_table, $source);
    }

    public function test_syntax_error() {
        $t = $this->tokenizer("some source.");
        try {
            $t->current();
            $this->assertTrue("This should not happen.");
        }
        catch (ParserException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_one_token() {
        $this->symbol_table->all_symbols[] = "\w+";
        $t = $this->tokenizer("hello");
        list($s,$m) = $t->current();
        $this->assertEquals(new Symbol("\w+", 0), $s);
        $this->assertEquals(array("hello"), $m);
    }

    public function test_two_tokens() {
        $this->symbol_table->all_symbols[] = "\w+";
        $t = $this->tokenizer("hello world");
        list($s1, $m1) = $t->current();
        $t->next();
        list($s2, $m2) = $t->current();
        $expected = new Symbol("\w+", 0);
        $this->assertEquals($expected, $s1);
        $this->assertEquals(array("hello"), $m1);
        $this->assertEquals($expected, $s2);
        $this->assertEquals(array("world"), $m2);
    }

    public function test_syntax_error2() {
        $this->symbol_table->all_symbols[] = "\d+";
        $t = $this->tokenizer("hello world");
        try {
            $t->current();
            $this->assertTrue("This should not happen.");
        }
        catch (ParserException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_rewind() {
        $this->symbol_table->all_symbols[] = "\w+";
        $t = $this->tokenizer("hello world");
        $t->next();
        $t->rewind();
        list($s,$m) = $t->current();
        $this->assertEquals(new Symbol("\w+", 0), $s);
        $this->assertEquals(array("hello"), $m);
    }

    public function test_key() {
        $this->symbol_table->all_symbols[] = "\w+";
        $t = $this->tokenizer("hello world");
        $p1 = $t->key();
        $t->next();
        $p2 = $t->key();
        $this->assertEquals(0, $p1);
        $this->assertEquals(1, $p2);
    }

    public function test_valid() {
        $this->symbol_table->all_symbols[] = "\w+";
        $t = $this->tokenizer("hello world");
        $this->assertTrue($t->valid());
        $t->next();
        $this->assertTrue($t->valid());
        $t->next();
        $this->assertFalse($t->valid());
    }
}
