<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Definition\AST;

class ASTTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->f = new AST\Factory();
    }

    public function test_root() {
        $r = $this->f->root([]);
        $this->assertInstanceOf(AST\Root::class, $r);
        $this->assertEquals([], $r->lines());
    }

    public function test_explanation() {
        $e = $this->f->explanation("EXPLANATION");
        $this->assertInstanceOf(AST\Explanation::class, $e);
        $this->assertEquals("EXPLANATION", $e->content());
    }

    public function test_root2() {
        $e1 = $this->f->explanation("1");
        $e2 = $this->f->explanation("2");
        $r = $this->f->root([$e1, $e2]);
        $this->assertEquals([$e1, $e2], $r->lines());
    }

    public function test_name() { 
        $n = $this->f->name("NAME");
        $this->assertInstanceOf(AST\Definition::class, $n);
        $this->assertInstanceOf(AST\Name::class, $n);
        $this->assertEquals("NAME", "$n");
    }

    public function test_assignment() {
        $n = $this->f->name("LEFT");
        $d = $this
            ->getMockBuilder(AST\Definition::class)
            ->getMock();
        $a = $this->f->assignment($n, $d);
        $this->assertInstanceOf(AST\Assignment::class, $a);
        $this->assertInstanceOf(AST\Line::class, $a);
        $this->assertEquals($a->name(), $n);
        $this->assertEquals($a->definition(), $d);
    }
}
