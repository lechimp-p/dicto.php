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

class Parser {
    public function __construct() {
        $this->symbol_table = new SymbolTable();
        $this->symbol_table
            ->add_symbol("\\d+", 0)
            ->null_denotation_is(function(array &$matches) {
                return intval($matches[0]);
            });
    }

    public function parse($source) {
        $this->tokenizer = new Tokenizer($this->symbol_table, $source); 
        $this->token = $this->tokenizer->current();
        return $this->expression(0);
    }

    protected function expression($right_binding_power) {
        list($t,$m) = $this->token;
        $this->tokenizer->next();
        list($nt,$nm) = $this->tokenizer->current();
        $left = $t->null_denotation($m);
        while ($right_binding_power < $nt->binding_power()) {
            $t = $nt; $m = $nm;
            $this->tokenizer->next();
            list($nt, $nm) = $this->tokenizer->current();
            $left = $t->left_denotation($left, $m);
        }
        return $left;
    }
}

class ParsingText extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->parser = new Parser();
    }

    public function test_1() {
        $res = $this->parser->parse("1");
        $this->assertEquals(1, $res);
    }
}
