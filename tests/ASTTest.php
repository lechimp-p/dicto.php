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

    protected function definition() {
        return $this
            ->getMockBuilder(AST\Definition::class)
            ->getMock();
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

    public function test_atom() {
        $a = $this->f->atom("atom");
        $this->assertInstanceOf(AST\Atom::class, $a);
        $this->assertEquals("atom", "$a");
    }

    public function test_atom2() {
        $a = $this->f->atom("at om");
        $this->assertInstanceOf(AST\Atom::class, $a);
        $this->assertEquals("at om", "$a");
    }

    public function test_no_atom() {
        try {
            $this->f->atom("ATOM");
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_no_atom2() {
        try {
            $this->f->atom("a=");
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_no_atom3() {
        try {
            $this->f->atom("a8");
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_property() {
        $d = $this->definition();
        $a = $this->f->atom("atom");
        $p = $this->f->property($d, $a, []);
        $this->assertInstanceOf(AST\Property::class, $p);
        $this->assertInstanceOf(AST\Definition::class, $p);
        $this->assertEquals($d, $p->left());
        $this->assertEquals($a, $p->id());
        $this->assertEquals([], $p->parameters());
    }

    public function test_except() {
        $l = $this->definition();
        $r = $this->definition();
        $e = $this->f->except($l, $r);
        $this->assertInstanceOf(AST\Except::class, $e);
        $this->assertInstanceOf(AST\Definition::class, $e);
        $this->assertEquals($l, $e->left());
        $this->assertEquals($r, $e->right());
    }

    public function test_any() {
        $b = $this->definition();
        $c = $this->definition();
        $a = $this->f->any([$b, $c]);
        $this->assertInstanceOf(AST\Any::class, $a);
        $this->assertInstanceOf(AST\Definition::class, $a);
        $this->assertEquals([$b, $c], $a->definitions());
    }

    public function test_assignment() {
        $n = $this->f->name("LEFT");
        $d = $this->definition();
        $a = $this->f->assignment($n, $d);
        $this->assertInstanceOf(AST\Assignment::class, $a);
        $this->assertInstanceOf(AST\Line::class, $a);
        $this->assertEquals($a->name(), $n);
        $this->assertEquals($a->definition(), $d);
    }
}
