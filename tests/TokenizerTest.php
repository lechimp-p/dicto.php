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
use Lechimp\Dicto\Definition\SymbolTable;
use Lechimp\Dicto\Definition\ParserException;

class SymbolTableMock extends SymbolTable {
    public $all_symbols = array();
    public function symbols() {
        foreach ($this->all_symbols as $symbol) {
            yield $symbol;
        }
    }
}

class TokenizerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->symbol_table = new SymbolTableMock();
        $this->noop = function (&$v) { return $v[0]; };
    }

    protected function tokenizer($source) {
        return new Tokenizer($this->symbol_table, $source);
    }

    public function test_syntax_error() {
        $t = $this->tokenizer("some source.");
        try {
            $s = $t->current();
            $this->assertTrue("This should not happen.");
        }
        catch (ParserException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_one_token() {
        $this->symbol_table->all_symbols[] = array("\w+", $this->noop);
        $t = $this->tokenizer("hello");
        $s = $t->current();
        $this->assertEquals("hello", $s);
    }

    public function test_two_tokens() {
        $this->symbol_table->all_symbols[] = array("\w+", $this->noop);
        $t = $this->tokenizer("hello world");
        $s1 = $t->current();
        $t->next();
        $s2 = $t->current();
        $this->assertEquals("hello", $s1);
        $this->assertEquals("world", $s2);
    }

    public function test_syntax_error2() {
        $this->symbol_table->all_symbols[] = array("\d+", $this->noop);
        $t = $this->tokenizer("hello world");
        try {
            $s = $t->current();
            $this->assertTrue("This should not happen.");
        }
        catch (ParserException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_rewind() {
        $this->symbol_table->all_symbols[] = array("\w+", $this->noop);
        $t = $this->tokenizer("hello world");
        $t->next();
        $t->rewind();
        $s = $t->current();
        $this->assertEquals("hello", $s);
    }

    public function test_key() {
        $this->symbol_table->all_symbols[] = array("\w+", $this->noop);
        $t = $this->tokenizer("hello world");
        $p1 = $t->key();
        $t->next();
        $p2 = $t->key();
        $this->assertEquals(0, $p1);
        $this->assertEquals(1, $p2);
    }

    public function test_valid() {
        $this->symbol_table->all_symbols[] = array("\w+", $this->noop);
        $t = $this->tokenizer("hello world");
        $this->assertTrue($t->valid());
        $t->next();
        $this->assertTrue($t->valid());
        $t->next();
        $this->assertFalse($t->valid());
    }
}
