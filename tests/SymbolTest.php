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
use Lechimp\Dicto\Definition\ParserException;

class SymbolText extends PHPUnit_Framework_TestCase {
    public function test_valid_regexp_only() {
        try {
            new Symbol("(a", 10);
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_no_null_denotation() {
        $s = new Symbol("a", 10);
        try {
            $arr = array("match");
            $s->null_denotation($arr);
            $this->assertFalse("This should not happen.");
        }
        catch (ParserException $e) {
            $this->assertTrue(true);
        }
    }


    public function test_null_denotation() {
        $s = new Symbol("a", 10);
        $s2 = $s->null_denotation_is(function(array $match) {
            return $match[0];
        });

        $this->assertEquals($s, $s2);

        $arr = array("match");
        $res = $s->null_denotation($arr);
        $this->assertEquals("match", $res);
    }

    public function test_no_left_denotation() {
        $s = new Symbol("a", 10);
        try {
            $arr = array("match");
            $s->left_denotation("foo", $arr);
            $this->assertFalse("This should not happen.");
        }
        catch (ParserException $e) {
            $this->assertTrue(true);
        }
    }


    public function test_left_denotation() {
        $s = new Symbol("a", 10);
        $s2 = $s->left_denotation_is(function($left, array $match) {
            return $left + $match[0];
        });

        $this->assertEquals($s, $s2);

        $arr = array(2);
        $res = $s->left_denotation(1, $arr);
        $this->assertEquals(3, $res);
    }
}
