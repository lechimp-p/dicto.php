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
use Lechimp\Dicto\Graph\Entity;
use Lechimp\Dicto\Graph\Node;
use Lechimp\Dicto\Graph\Relation;

class Graph_QueryTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->g = new Graph(); 
    }

    public function test_filter() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);

        $res = $this->g->query()
            ->filter(function(Node $n, &$_) {
                return $n->type() == "a_type";
            })
            ->extract(function(Node $n, array &$result) {
                $result[] = $n;
            })
            ->run([]);

        $this->assertEquals([[$n1]], $res);
    }

    public function test_match_all() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);

        $res = $this->g->query()
            ->extract(function(Node $n, array &$result) {
                $result[] = $n;
            })
            ->run([]);

        $this->assertEquals([[$n1],[$n2]], $res);
    }

    public function test_match_none() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);

        $res = $this->g->query()
            ->expand(function(Node $n) {
                return [];
            })
            ->extract(function(Node $n, array &$result) {
                $result[] = $n;
            })
            ->run([]);

        $this->assertEquals([], $res);
    }

    public function test_path1() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);
        $rel = $this->g->add_relation($n1, "rel_type", [], $n2);

        $extract_self = function(Entity $e, array &$res) { $res[] = $e; };
        $all_relations = function(Node $n) { return $n->relations(); };
        $target = function(Relation $r) { return [$r->target()]; };

        $res = $this->g->query()
            ->extract($extract_self)
            ->expand($all_relations)
            ->extract($extract_self)
            ->expand($target)
            ->extract($extract_self)
            ->run([]);

        $this->assertEquals([[$n1,$rel,$n2]], $res);
    }

    public function test_path2() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);
        $n3 = $this->g->create_node("c_type", []);
        $r1 = $this->g->add_relation($n1, "rel_A", [], $n2);
        $r2 = $this->g->add_relation($n2, "rel_B", [], $n3);

        $extract_self = function(Entity $e, array &$res) { $res[] = $e; };
        $all_relations = function(Node $n) { return $n->relations(); };
        $target = function(Relation $r) { return [$r->target()]; };

        $res = $this->g->query()
            ->extract($extract_self)
            ->expand($all_relations)
            ->extract($extract_self)
            ->expand(function($r) {
                if ($r->type() == "rel_B") {
                    return [$r->target()];
                }
                else {
                    return [];
                }
            })
            ->extract($extract_self)
            ->run([]);

        $this->assertEquals([[$n2,$r2,$n3]], $res);
    }

    public function test_paths() {
        $n1 = $this->g->create_node("a_type", []);
        $n2 = $this->g->create_node("b_type", []);
        $n3 = $this->g->create_node("c_type", []);
        $r1 = $this->g->add_relation($n1, "rel_A", [], $n2);
        $r2 = $this->g->add_relation($n2, "rel_A", [], $n3);

        $extract_self = function(Entity $e, array &$res) { $res[] = $e; };
        $all_relations = function(Node $n) { return $n->relations(); };
        $target = function(Relation $r) { return [$r->target()]; };

        $res = $this->g->query()
            ->extract($extract_self)
            ->expand($all_relations)
            ->extract($extract_self)
            ->expand($target)
            ->extract($extract_self)
            ->run([]);

        $this->assertEquals([[$n1,$r1,$n2],[$n2,$r2,$n3]], $res);
    }
}
