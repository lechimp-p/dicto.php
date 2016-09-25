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
            ->files()
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
            ->classes()
            ->extract(function($n, &$r) {
                $r["name"] = $n->property("name");
            })
            ->expand_relation(["defined in"])
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
            ->methods()
            ->extract(function($n, &$r) {
                $r["name"] = $n->property("name");
                $r["class"] = $n->related_nodes(function($r) {
                    return $r->type() == "contained in";
                });
            })
            ->expand_relation(["defined in"])
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
            ->classes()
            ->expand_relation(["contains"])
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
            ->functions()
            ->extract(function($n, &$r) {
                $r["name"] = $n->property("name");
            })
            ->expand_relation(["defined in"])
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
            ->filter_by_type("global")
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
            ->filter_by_type("global")
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
            ->filter_by_type("language construct")
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
            ->filter_by_type("language construct")
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
            ->filter_by_type("method reference")
            ->extract(function($n,&$r) {
                $r["name"] = $n->property("name");
            })
            ->expand_relation(["referenced at"])
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
            ->filter_by_type("function reference")
            ->extract(function($n,&$r) {
                $r["name"] = $n->property("name");
            })
            ->expand_relation(["referenced at"])
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
            ->functions()
            ->extract(function($n, &$r) {
                $r["l"] = $n;
            })
            ->expand_relation(["related to"])
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

/*    public function test_insert_source() {
        $this->db->source("foo.php", "FOO\nBAR");

        $res = (new _Query)
            ->with_filter(function(Node $n) {
                return $n->type() == "file";
            })
            ->with_filter(function(Relation $r) {
                return $r->type() == "contains";
            })
            ->with_filter(function(Node $n) {
                return $n->type() == "line";
            })
            ->execute_on($this->db)
            ->extract(function($f,$_, $l) {
                return
                    [ "path" => $f->property("path")
                    , "line" => $l->property("num")
                    , "source" => $l->property("source")
                    ];
            });
        $expected =
            [
                [ "path" => "foo.php"
                , "line" => "1"
                , "source" => "FOO"
                ]
            ,
                [ "path" => "foo.php"
                , "line" => "2"
                , "source" => "BAR"
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_insert_definition() {
        $this->db->source("AClass.php", "FOO\nBAR");
        $this->db->definition("AClass", Variable::CLASS_TYPE, "AClass.php", 1, 2);

        $res = (new _Query)
            ->with_filter(function(Node $n) {
                return $n->type() == "name";
            })
            ->with_filter(function(Relation $r) {
                return $r->type() == "has_definition";
            })
            ->with_filter(function(Node $n) {
                return $n->type() == "definition";
            })
            ->with_filter(function(Relation $r) {
                return $r->type("in_file");
            })
            ->with_filter(function(Node $n) {
                return $n->type("file");
            })
            ->execute_on($this->db)
            ->extract(function($n, $_1, $d, $_2, $f) {
                return
                    [ "name" => $n->property("name")
                    , "type" => $n->property("type")
                    , "path" => $f->property("path")
                    , "start_line" => $d->property("start_line")
                    , "end_line" => $d->property("end_line")
                    ];
            });

        $expected =
            [
                [ "name" => "AClass"
                , "type" => Variable::CLASS_TYPE
                , "path" => "AClass.php"
                , "start_line" => "1"
                , "end_line" => "2"
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_insert_name() {
        $id = $this->db->name("AClass", Variable::CLASS_TYPE);

        $res = (new _Query)
            ->with_filter(function(Node $n) {
                return $n->type() == "name";
            })
            ->execute_on($this->db)
            ->extract(function($n) {
                return
                    [ "id" => $n->id()
                    , "name" => $n->property("name")
                    , "type" => $n->property("type")
                    ];
            });
        $expected =
            [
                [ "id" => $id
                , "name" => "AClass"
                , "type" => Variable::CLASS_TYPE
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_insert_some_relation() {
        $this->db->source("AClass.php", "FOO\nBAR");
        $this->db->source("BClass.php", "FOO\nBAR");
        $id1 = $this->db->name("AClass", Variable::CLASS_TYPE);
        $id2 = $this->db->name("BClass", Variable::CLASS_TYPE);
        $this->db->relation($id1, $id2, "some_relation", "BClass.php", 1);

        $res = (new _Query)
            ->with_filter(function(Node $n) {
                return $n->type() == "name";
            })
            ->with_filter(function(Relation $n) {
                return true;
            })
            ->with_filter(function(Node $n) {
                return $n->type() == "name";
            })
            ->execute_on($this->db)
            ->extract(function($nl,$r,$nr) {
                return
                    [ "name_left" => $nl->id()
                    , "name_right" => $nr->id()
                    , "which" => $r->type()
                    , "path" => $r->property("path")
                    , "line" => $r->property("line")
                    ];
            });
        $expected =
            [
                [ "name_left" => "$id1"
                , "name_right" => "$id2"
                , "which" => "some_relation"
                , "path" => "BClass.php"
                , "line" => "1"
                ]
            ];

        $this->assertEquals($expected, $res);
    }

    public function test_retreive_name_id() {
        $id1 = $this->db->name("AClass", Variable::CLASS_TYPE);
        $id2 = $this->db->name("AClass", Variable::CLASS_TYPE);
        $this->assertInternalType("integer", $id1);
        $this->assertEquals($id1, $id2);
    }

    public function test_retreive_file_id() {
        $id1 = $this->db->file("AClass.php");
        $id2 = $this->db->file("AClass.php");
        $this->assertInternalType("integer", $id1);
        $this->assertEquals($id1, $id2);
    }

    public function test_retreive_name_of_definition() {
        $this->db->source("AClass.php", "FOO\nBAR");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "AClass.php", 1, 2);
        $id2 = $this->db->name("AClass", Variable::CLASS_TYPE);
        $this->assertEquals($id1, $id2);
    }

    public function test_retreive_file_of_source() {
        $id1 = $this->db->source("AClass.php", "");
        $id2 = $this->db->file("AClass.php");
        $this->assertInternalType("integer", $id1);
        $this->assertEquals($id1, $id2);
    }

    public function test_insert_two_method_definitions() {
        $this->db->source("AClass.php", "FOO\nBAR");
        $this->db->source("BClass.php", "FOO\nBAR");
        $this->db->definition("a_method", Variable::METHOD_TYPE, "AClass.php", 1, 2);
        $this->db->definition("a_method", Variable::METHOD_TYPE, "BClass.php", 1, 2);

        $res = (new _Query)
            ->with_filter(function(Node $n) {
                return $n->type() == "name";
            })
            ->with_filter(function(Relation $r) {
                return $r->type() == "has_definition";
            })
            ->with_filter(function(Node $n) {
                return $n->type() == "definition";
            })
            ->with_filter(function(Relation $r) {
                return $r->type() == "in_file";
            })
            ->with_filter(function(Node $n) {
                return $n->type() == "file";
            })
            ->execute_on($this->db)
            ->extract(function($n, $_1, $d, $_2, $f) {
                return
                    [ "name" => $n->property("name")
                    , "type" => $n->property("type")
                    , "path" => $f->property("path")
                    , "start_line" => $d->property("start_line")
                    , "end_line" => $d->property("end_line")
                    ];
            });
        $expected =
            [
                [ "name" => "a_method"
                , "type" => Variable::METHOD_TYPE
                , "path" => "AClass.php"
                , "start_line" => "1"
                , "end_line" => "2"
                ]
            ,
                [ "name" => "a_method"
                , "type" => Variable::METHOD_TYPE
                , "path" => "BClass.php"
                , "start_line" => "1"
                , "end_line" => "2"
                ]
            ];
        $this->assertEquals($expected, $res);
    }

    public function test_insert_two_relations() {
        $this->db->source("AClass.php", "FOO\nBAR");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "AClass.php", 1, 2);
        $id2 = $this->db->name("AClass", Variable::CLASS_TYPE);
        $this->db->relation($id1, $id2, "some_relation", "AClass.php", 1);
        $this->db->relation($id1, $id2, "some_relation", "AClass.php", 2);

        $res = (new _Query)
            ->with_filter(function(Node $n) {
                return $n->type() == "name";
            })
            ->with_filter(function(Relation $n) {
                return true;
            })
            ->with_filter(function(Node $n) {
                return $n->type() == "name";
            })
            ->execute_on($this->db)
            ->extract(function($nl,$r,$nr) {
                return
                    [ "name_left" => $nl->id()
                    , "name_right" => $nr->id()
                    , "which" => $r->type()
                    , "path" => $r->property("path")
                    , "line" => $r->property("line")
                    ];
            });
        $expected =
            [
                [ "name_left" => "$id1"
                , "name_right" => "$id2"
                , "which" => "some_relation"
                , "path" => "AClass.php"
                , "line" => "1"
                ]
            ,
                [ "name_left" => "$id1"
                , "name_right" => "$id2"
                , "which" => "some_relation"
                , "path" => "AClass.php"
                , "line" => "2"
                ]
            ];

        $this->assertEquals($expected, $res);
    }

    public function test_insert_two_relations_same_line() {
        $this->db->source("AClass.php", "FOO\nBAR");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "AClass.php", 1, 2);
        $id2 = $this->db->name("AClass", Variable::CLASS_TYPE);
        $this->db->relation($id1, $id2, "some_relation", "AClass.php", 1);
        $this->db->relation($id1, $id2, "some_relation", "AClass.php", 1);

        $res = (new _Query)
            ->with_filter(function(Node $n) {
                return $n->type() == "name";
            })
            ->with_filter(function(Relation $n) {
                return true;
            })
            ->with_filter(function(Node $n) {
                return $n->type() == "name";
            })
            ->execute_on($this->db)
            ->extract(function($nl,$r,$nr) {
                return
                    [ "name_left" => $nl->id()
                    , "name_right" => $nr->id()
                    , "which" => $r->type()
                    , "path" => $r->property("path")
                    , "line" => $r->property("line")
                    ];
            });
        $expected =
            [
                [ "name_left" => "$id1"
                , "name_right" => "$id2"
                , "which" => "some_relation"
                , "path" => "AClass.php"
                , "line" => "1"
                ]
            ,
                [ "name_left" => "$id1"
                , "name_right" => "$id2"
                , "which" => "some_relation"
                , "path" => "AClass.php"
                , "line" => "1"
                ]
            ];

        $this->assertEquals($expected, $res);
    }

    public function test_method_info() {
        $this->db->source("AClass.php", "FOO\nBAR");
        list($cls_id, $_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "AClass.php", 1, 2);
        list($mtd_id, $def_id) = $this->db->definition("a_method", Variable::METHOD_TYPE, "AClass.php", 1, 2);
        $this->db->method_info($mtd_id, $cls_id, $def_id);

        $res = (new _Query)
            ->with_filter(function(Node $n) {
                return $n->type() == "name"
                    && $n->property("type") == "methods";
            })
            ->with_filter(function(Relation $r) {
                return $r->type("in_class");
            })
            ->with_filter(function(Node $n) {
                return $n->type() == "name"
                    && $n->property("type") == "classes";
            })
            ->execute_on($this->db)
            ->extract(function($m,$r,$c) {
                return
                    [ "name" => $m->id()
                    , "class" => $c->id()
                    , "definition" => $r->property("def_id")
                    ];
            });
        $expected =
            [
                [ "name"        => $mtd_id
                , "class"       => $cls_id
                , "definition"  => $def_id
                ]
            ];
        $this->assertEquals($expected, $res);
    }*/
}
