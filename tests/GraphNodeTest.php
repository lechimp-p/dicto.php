<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Graph\Node;

class GraphNodeTest extends PHPUnit_Framework_TestCase {
    public function test_id() {
        $e = new Node(1, "a_type", array());
        $this->assertEquals(1, $e->id());
    }

    public function test_type() {
        $e = new Node(1, "a_type", array());
        $this->assertEquals("a_type", $e->type());
    }

    public function test_properties() {
        $e = new Node(1, "a_type", ["prop" => "value"]);
        $this->assertEquals(["prop" => "value"], $e->properties());
    }

    public function test_relations() {
        $l = new Node(1, "a_type", ["prop" => "value"]);
        $r = new Node(2, "a_type", ["prop" => "value"]);
        $l->add_relation("rel_type", ["is" => "rel"], $r);
        $rels = $l->relations();
        $this->assertCount(1,$rels);
        $rel = $rels[0];
        $this->assertEquals("rel_type", $rel->type());
        $this->assertEquals(["is" => "rel"], $rel->properties());
        $this->assertSame($r, $rel->target());
    }
}
