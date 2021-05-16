<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Graph\Relation;
use Lechimp\Dicto\Graph\Node;

class GraphRelationTest extends \PHPUnit\Framework\TestCase {
    public function test_type() {
        $t = new Node(0, "some_type", []);
        $e = new Relation("a_type", array(), $t);

        $this->assertEquals("a_type", $e->type());
    }

    public function test_properties() {
        $t = new Node(0, "some_type", []);
        $e = new Relation("a_type", ["prop" => "value"], $t);

        $this->assertEquals(["prop" => "value"], $e->properties());
    }

    public function test_target() {
        $t = new Node(0, "some_type", []);
        $e = new Relation("a_type", ["prop" => "value"], $t);

        $this->assertSame($t, $e->target());
    }
}
