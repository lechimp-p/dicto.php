<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Graph\Graph;
use Lechimp\Dicto\Graph\Node;

class GraphTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->g = new Graph(); 
    }

    public function test_add_nodes() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);
        $ns = iterator_to_array($this->g->nodes());

        $this->assertCount(2, $ns);
        $this->assertSame($n1, $ns[0]); 
        $this->assertSame($n2, $ns[1]); 
        $this->assertInstanceOf(Node::class, $n1);
        $this->assertInstanceOf(Node::class, $n2);
    }

    public function test_node_props() {
        $props = 
            [ "some" => "prop"
            , "some_oth" => "er_prop"
            ];
        $n1 = $this->g->create_node("a_type", $props);

        $this->assertEquals("a_type", $n1->type());
        $this->assertEquals($props, $n1->properties()); 
    }

    public function test_node_ids() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);

        $this->assertEquals(0, $n1->id());
        $this->assertEquals(1, $n2->id());
    } 

    public function test_relation() {
        $l = $this->g->create_node("a_type", []);
        $r = $this->g->create_node("b_type", []);
        $props =
            [ "some" => "prop"
            , "some_oth" => "er_prop"
            ];
        $rl1 = $this->g->add_relation($l, "rel_type", $props, $r);
        $rl2 = $this->g->add_relation($l, "rel_type2", [], $l);

        $rels = $l->relations();
        $this->assertCount(2, $rels);

        $rel1 = $rels[0];
        $this->assertSame($rl1, $rel1);
        $this->assertEquals("rel_type", $rel1->type());
        $this->assertEquals($props, $rel1->properties());
        $this->assertSame($r, $rel1->target());

        $rel2 = $rels[1];
        $this->assertSame($rl2, $rel2);
        $this->assertEquals("rel_type2", $rel2->type());
        $this->assertEquals([], $rel2->properties());
        $this->assertSame($l, $rel2->target());
    }

    public function test_nodes() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);

        $this->assertEquals([$n1,$n2], iterator_to_array($this->g->nodes()));
    }

    public function test_filtered_nodes() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);
        $matcher = function (Node $n) {
            return $n->type() == "b_type";
        };

        $this->assertEquals([$n2], iterator_to_array($this->g->nodes($matcher)));
    }

    public function test_node() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);

        $this->assertEquals($n1, $this->g->node($n1->id()));
        $this->assertEquals($n2, $this->g->node($n2->id()));
        try {
            $this->g->node(42);
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {}
    }

    public function test_no_initial_props() {
        $n1 = $this->g->create_node("a_type");

        $this->assertInstanceOf(Node::class, $n1);
    }
}
