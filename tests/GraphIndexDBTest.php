<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\Graph\IndexDB;
use Lechimp\Dicto\Graph\_Query;
use Lechimp\Dicto\Graph\Node;
use Lechimp\Dicto\Graph\Relation;

class GraphIndexDBTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->db = new IndexDB();
    }

    public function test_file() {
        $this->db->_file("some_path.php", "A\nB");

        $res = $this->db->query()
            ->filter_by_types(["file"])
            ->extract(function($n, &$r) {
                $r["path"] = $n->property("path");
                $r["source"] = $n->property("source");
            })
            ->run([]);

        $expected =
            [   [ "path" => "some_path.php"
                , "source" => ["A","B"]
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_class() {
        $file = $this->db->_file("some_path.php", "A\nB");
        $this->db->_class("AClass", $file, 1, 2);

        $res = $this->db->query()
            ->filter_by_types(["class"])
            ->extract(function($n, &$r) {
                $r["name"] = $n->property("name");
            })
            ->expand_relations(["defined in"])
            ->extract(function($e,&$r) {
                $r["start_line"] = $e->property("start_line");
                $r["end_line"] = $e->property("end_line");
            })
            ->expand_target()
            ->extract(function($e,&$r) {
                $r["file"] = $e;
            })
            ->run([]);

        $expected =
            [   [ "name" => "AClass"
                , "file" => $file
                , "start_line" => 1
                , "end_line" => 2
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_method() {
        $file = $this->db->_file("some_path.php", "A\nB\nC\nD");
        $class = $this->db->_class("AClass", $file, 1, 4);
        $this->db->_method("a_method", $class, $file, 2, 3);

        $res = $this->db->query()
            ->filter_by_types(["method"])
            ->extract(function($n, &$r) {
                $r["name"] = $n->property("name");
                $r["class"] = $n->related_nodes(function($r) {
                    return $r->type() == "contained in";
                });
            })
            ->expand_relations(["defined in"])
            ->extract(function($e,&$r) {
                $r["start_line"] = $e->property("start_line");
                $r["end_line"] = $e->property("end_line");
            })
            ->expand_target()
            ->extract(function($e,&$r) {
                $r["file"] = $e;
            })
            ->run([]);

        $expected =
            [   [ "name" => "a_method"
                , "class" => [$class]
                , "file" => $file
                , "start_line" => 2
                , "end_line" => 3
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_method_class_rel() {
        $file = $this->db->_file("some_path.php", "A\nB\nC\nD");
        $class = $this->db->_class("AClass", $file, 1, 4);
        $method = $this->db->_method("a_method", $class, $file, 2, 3);

        $res = $this->db->query()
            ->filter_by_types(["class"])
            ->expand_relations(["contains"])
            ->expand_target()
            ->extract(function($n, &$r) {
                $r["method"] = $n;
            })
            ->run([]);

        $expected =
            [   [ "method" => $method
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_function() {
        $file = $this->db->_file("some_path.php", "A\nB\nC\nD");
        $this->db->_function("a_function", $file, 2, 3);

        $res = $this->db->query()
            ->filter_by_types(["function"])
            ->extract(function($n, &$r) {
                $r["name"] = $n->property("name");
            })
            ->expand_relations(["defined in"])
            ->extract(function($e,&$r) {
                $r["start_line"] = $e->property("start_line");
                $r["end_line"] = $e->property("end_line");
            })
            ->expand_target()
            ->extract(function($e,&$r) {
                $r["file"] = $e;
            })
            ->run([]);

        $expected =
            [   [ "name" => "a_function"
                , "file" => $file
                , "start_line" => 2
                , "end_line" => 3
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_global() {
        $this->db->_global("some_global");

        $res = $this->db->query()
            ->filter_by_types(["global"])
            ->extract(function($n,&$r) {
                $r["name"] = $n->property("name");
            })
            ->run([]);

        $expected =
            [   [ "name" => "some_global"
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_insert_global_twice() {
        $ga = $this->db->_global("some_global");
        $gb = $this->db->_global("some_global");

        $res = $this->db->query()
            ->filter_by_types(["global"])
            ->extract(function($n,&$r) {
                $r["name"] = $n->property("name");
            })
            ->run([]);

        $expected =
            [   [ "name" => "some_global"
                ]
            ];
        $this->assertEquals($expected, $res);
        $this->assertSame($ga, $gb);
    }

    public function test_language_construct() {
        $this->db->_language_construct("die");

        $res = $this->db->query()
            ->filter_by_types(["language construct"])
            ->extract(function($n,&$r) {
                $r["name"] = $n->property("name");
            })
            ->run([]);

        $expected =
            [   [ "name" => "die"
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_insert_language_construct_twice() {
        $la = $this->db->_language_construct("die");
        $lb = $this->db->_language_construct("die");

        $res = $this->db->query()
            ->filter_by_types(["language construct"])
            ->extract(function($n,&$r) {
                $r["name"] = $n->property("name");
            })
            ->run([]);

        $expected =
            [   [ "name" => "die"
                ]
            ];
        $this->assertEquals($expected, $res);
        $this->assertSame($la, $lb);
    }

    public function test_method_reference() {
        $file = $this->db->_file("some_path.php", "A\nB\nC\nD");
        $this->db->_method_reference("some_method", $file, 2);

        $res = $this->db->query()
            ->filter_by_types(["method reference"])
            ->extract(function($n,&$r) {
                $r["name"] = $n->property("name");
            })
            ->expand_relations(["referenced at"])
            ->extract(function($n,&$r) {
                $r["line"] = $n->property("line");
            })
            ->expand_target()
            ->extract(function($n,&$r) {
                $r["file"] = $n;
            })
            ->run([]);

        $expected =
            [   [ "name" => "some_method"
                , "line" => 2
                , "file" => $file
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_function_reference() {
        $file = $this->db->_file("some_path.php", "A\nB\nC\nD");
        $this->db->_function_reference("some_function", $file, 2);

        $res = $this->db->query()
            ->filter_by_types(["function reference"])
            ->extract(function($n,&$r) {
                $r["name"] = $n->property("name");
            })
            ->expand_relations(["referenced at"])
            ->extract(function($n,&$r) {
                $r["line"] = $n->property("line");
            })
            ->expand_target()
            ->extract(function($n,&$r) {
                $r["file"] = $n;
            })
            ->run([]);

        $expected =
            [   [ "name" => "some_function"
                , "line" => 2
                , "file" => $file
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_relation() {
        $file = $this->db->_file("some_path.php", "A\nB\nC\nD");
        $l = $this->db->_function("a_function", $file, 2, 3);
        $r = $this->db->_function_reference("some_function", $file, 2);
        $this->db->_relation($l, "related to", $r, $file, 3);

        $res = $this->db->query()
            ->filter_by_types(["function"])
            ->extract(function($n, &$r) {
                $r["l"] = $n;
            })
            ->expand_relations(["related to"])
            ->extract(function($e,&$r) {
                $r["line"] = $e->property("line");
                $r["file"] = $e->property("file");
            })
            ->expand_target()
            ->extract(function($e,&$r) {
                $r["r"] = $e;
            })
            ->run([]);

        $expected =
            [   [ "l" => $l
                , "line" => 3
                , "file" => $file
                , "r" => $r
                ]
            ];
        $this->assertEquals($expected, $res);
    }
}
