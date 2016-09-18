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
use Lechimp\Dicto\Graph\Path;

class GraphPathTest extends PHPUnit_Framework_TestCase {
    public function test_extract() {
        $n1 = new Node(0, "a_type", ["node1" => "prop1"]);
        $n2 = new Node(1, "b_type", ["node2" => "prop2"]);
        $r = $n1->add_relation("a_rel", ["rel" => "rel_prop"], $n2);

        $path = new Path($n1);
        $path->append($r);
        $path->append($n2);
        $res = $path->extract(function($a,$b,$c) {
            return
                [ "node1_type" => $a->type()
                , "node1_prop" => $a->property("node1")
                , "rel_type" => $b->type()
                , "rel_prop" => $b->property("rel")
                , "node2_type" => $c->type()
                , "node2_prop" => $c->property("node2")
                ];
        });
        $expected =
            [ "node1_type" => "a_type"
            , "node1_prop" => "prop1"
            , "rel_type" => "a_rel"
            , "rel_prop" => "rel_prop"
            , "node2_type" => "b_type"
            , "node2_prop" => "prop2"
            ];
        $this->assertEquals($expected, $res);
    }
}
