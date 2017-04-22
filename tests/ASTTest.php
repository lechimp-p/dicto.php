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
}
