<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Definition\Parser as ParserBase;
use Lechimp\Dicto\Definition\Tokenizer;
use Lechimp\Dicto\Definition\SymbolTable;

class Parser extends ParserBase {
    public function __construct() {
        parent::__construct();
        $this->literal("\\d+", function(array &$matches) {
                return intval($matches[0]);
            });
        $this->operator("+", 10)
            ->left_denotation_is(function($left, array &$matches) {
                return $left + $this->expression(10);
            });
        $this->operator("-", 10)
            ->left_denotation_is(function($left, array &$matches) {
                return $left - $this->expression(10);
            });
        $this->operator("**", 30)
            ->left_denotation_is(function($left, array &$matches) {
                return pow($left, $this->expression(30-1));
            });
        $this->operator("*", 20)
            ->left_denotation_is(function($left, array &$matches) {
                return $left * $this->expression(20);
            });
        $this->operator("/", 20)
            ->left_denotation_is(function($left, array &$matches) {
                return $left / $this->expression(20);
            });
        $this->operator("(")
            ->null_denotation_is(function(array &$matches) {
                $res = $this->expression(0);
                $this->advance_operator(")");
                return $res;
            });
        $this->operator(")");
        $this->symbol("\n")
            ->left_denotation_is(function($left, array &$matches) {
                die("here");
                $this->results[] = $left;
                return $this->expression(0);
            });
    }

    protected function root() {
        // Empty file
        if ($this->is_end_of_file_reached()) {// End of file
            return array();
        }

        $res = array();
        while (true) {
            $res[] = $this->expression(0);
            if ($this->is_end_of_file_reached()) {
                return $res;
            }
            $this->advance("\n");
        }
    }

    protected function expression($right_binding_power) {
        $t = $this->current_symbol();
        $m = $this->current_match();
        $this->fetch_next_token();
        $left = $t->null_denotation($m);

        while ($right_binding_power < $this->token[0]->binding_power()) {
            $t = $this->current_symbol();
            $m = $this->current_match();
            $this->fetch_next_token();
            $left = $t->left_denotation($left, $m);
        }
        return $left;
    }
}

class ParsingTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->parser = new Parser();
    }

    public function parse($expr) {
        return $this->parser->parse($expr);
    }

    public function test_1() {
        $res = $this->parse("1");
        $this->assertEquals(array(1), $res);
    }

    public function test_add() {
        $res = $this->parse("1 + 2");
        $this->assertEquals(array(3), $res);
    }

    public function test_subtract() {
        $res = $this->parse("1 - 2");
        $this->assertEquals(array(-1), $res);
    }

    public function test_multiply() {
        $res = $this->parse("2 * 3");
        $this->assertEquals(array(6), $res);
    }

    public function test_binding() {
        $res = $this->parse("2 * 3 - 1");
        $this->assertEquals(array(5), $res);
    }

    public function test_pow() {
        $res = $this->parse("2 ** 3");
        $this->assertEquals(array(8), $res);
    }

    public function test_right_binding() {
        $res = $this->parse("2 ** 3 ** 2");
        $this->assertEquals(array(512), $res);
    }

    public function test_parantheses() {
        $res = $this->parse("2 * ( 3 - 1 )");
        $this->assertEquals(array(4), $res);
    }

    public function test_parantheses_2() {
        $res = $this->parse("( 3 - 1 )");
        $this->assertEquals(array(2), $res);
    }

    public function test_no_space() {
        $res = $this->parse("(3-1)");
        $this->assertEquals(array(2), $res);
    }

    public function test_empty() {
        $res = $this->parse("");
        $this->assertEquals(array(), $res);
    }

    public function test_multiline() {
        $res = $this->parse("1-2\n4-3");
        $this->assertEquals(array(-1,1), $res);
    }
}
