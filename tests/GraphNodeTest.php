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
use Lechimp\Dicto\Graph\Relation;

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

    public function test_relations1() {
        $l = new Node(1, "a_type", ["prop" => "value"]);
        $r = new Node(2, "a_type", ["prop" => "value"]);
        $rl = $l->add_relation("rel_type", ["is" => "rel"], $r);
        $rels = $l->relations();

        $this->assertCount(1,$rels);
        $rel = $rels[0];
        $this->assertSame($rl, $rel);
        $this->assertEquals("rel_type", $rel->type());
        $this->assertEquals(["is" => "rel"], $rel->properties());
        $this->assertSame($r, $rel->target());
    }

    public function test_relations2() {
        $a = new Node(1, "a_type", ["prop" => "value"]);
        $b = new Node(2, "a_type", ["prop" => "value"]);
        $c = new Node(3, "a_type", ["prop" => "value"]);
        $r1 = $a->add_relation("rel_type", ["is" => "rel"], $b);
        $r2 = $a->add_relation("rel_type2", ["is" => "rel2"], $c);
        $rels = $a->relations(function(Relation $r) {
            return $r->type() == "rel_type2";
        });

        $this->assertCount(1,$rels);
        $rel = $rels[0];
        $this->assertSame($r2, $rel);
        $this->assertEquals("rel_type2", $rel->type());
        $this->assertEquals(["is" => "rel2"], $rel->properties());
        $this->assertSame($c, $rel->target());
    }

    public function test_related_nodes1() {
        $a = new Node(1, "a_type", ["prop" => 1]);
        $b = new Node(2, "a_type", ["prop" => 2]);
        $c = new Node(3, "a_type", ["prop" => 3]);
        $a->add_relation("rel_type_1", ["is" => "rel"], $b);
        $a->add_relation("rel_type_2", ["is" => "rel"], $c);

        $res = $a->related_nodes();
        $this->assertCount(2, $res);
        $this->assertSame($b, $res[0]);
        $this->assertSame($c, $res[1]);
    }

    public function test_related_nodes2() {
        $a = new Node(1, "a_type", ["prop" => 1]);
        $b = new Node(2, "a_type", ["prop" => 2]);
        $c = new Node(3, "a_type", ["prop" => 3]);
        $a->add_relation("rel_type_1", ["is" => "rel"], $b);
        $a->add_relation("rel_type_2", ["is" => "rel"], $c);

        $res = $a->related_nodes(function(Relation $r) {
            return $r->type() == "rel_type_1";
        });
        $this->assertCount(1, $res);
        $this->assertSame($b, $res[0]);
    }

    public function test_related_nodes3() {
        $a = new Node(1, "a_type", ["prop" => 1]);
        $b = new Node(2, "a_type", ["prop" => 2]);
        $c = new Node(3, "a_type", ["prop" => 3]);
        $a->add_relation("rel_type_1", ["is" => "rel"], $b);
        $a->add_relation("rel_type_2", ["is" => "rel"], $c);

        $res = $a->related_nodes(function(Relation $r) {
            return $r->type() == "rel_type_2";
        });
        $this->assertCount(1, $res);
        $this->assertSame($c, $res[0]);
    }


    public function test_related_nodes4() {
        $a = new Node(1, "a_type", ["prop" => 1]);
        $b = new Node(2, "a_type", ["prop" => 2]);
        $c = new Node(3, "a_type", ["prop" => 3]);
        $a->add_relation("rel_type_1", ["is" => "good"], $b);
        $a->add_relation("rel_type_1", ["is" => "bad"], $c);

        $res = $a->related_nodes(function(Relation $r) {
            return $r->property("is") == "good";
        });
        $this->assertCount(1, $res);
        $this->assertSame($b, $res[0]);
    }
}
