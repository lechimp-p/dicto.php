<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Regexp;
use Lechimp\Dicto\Graph\PredicateFactory;
use Lechimp\Dicto\Graph\Predicate;
use Lechimp\Dicto\Graph\Graph;

class GraphPredicateTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->f = new PredicateFactory();
        $this->g = new Graph();
    }

    public function test_creation() {
        $f = $this->f;
        $pred = $f->_and
            ([ $f->_or
                ([ $f->_not($f->_type_is("foo"))
                 , $f->_false()
                ])
             , $f->_true()
             , $f->_property("bar")->_matches(new Regexp(".*"))
             , $f->_custom(function(Entity $e) { return true; })
            ]);
        $this->assertInstanceOf(Predicate::class, $pred);
    }

    public function test_compile_true() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $true = $this->f->_true();
        $compiled = $true->compile();

        $this->assertTrue($compiled($n1));
        $this->assertTrue($compiled($n2));
        $this->assertTrue($compiled($r));
    }

    public function test_compile_false() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $true = $this->f->_false();
        $compiled = $true->compile();

        $this->assertFalse($compiled($n1));
        $this->assertFalse($compiled($n2));
        $this->assertFalse($compiled($r));
    }

    public function test_compile_true_or_false() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $or = $f->_or([$f->_true(), $f->_false()]);
        $compiled = $or->compile();

        $this->assertTrue($compiled($n1));
        $this->assertTrue($compiled($n2));
        $this->assertTrue($compiled($r));
    }

    public function test_compile_false_or_true() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $or = $f->_or([$f->_false(), $f->_true()]);
        $compiled = $or->compile();

        $this->assertTrue($compiled($n1));
        $this->assertTrue($compiled($n2));
        $this->assertTrue($compiled($r));
    }

    public function test_compile_false_or_false() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $or = $f->_or([$f->_false(), $f->_false()]);
        $compiled = $or->compile();

        $this->assertFalse($compiled($n1));
        $this->assertFalse($compiled($n2));
        $this->assertFalse($compiled($r));
    }

    public function test_compile_false_or_true_or_false() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $or = $f->_or([$f->_false(), $f->_true(), $f->_false()]);
        $compiled = $or->compile();

        $this->assertTrue($compiled($n1));
        $this->assertTrue($compiled($n2));
        $this->assertTrue($compiled($r));
    }

    public function test_or_short_circuit() {
        $p = $this->f->_true();
        $p2 = $this->f->_or([$p]);
        $this->assertSame($p, $p2);
    }

    public function test_or_empty() {
        try {
            $this->f->_or([]);
            $this->assertFalse("This should not happen!");
        }
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_compile_true_and_false() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $and = $f->_and([$f->_true(), $f->_false()]);
        $compiled = $and->compile();

        $this->assertFalse($compiled($n1));
        $this->assertFalse($compiled($n2));
        $this->assertFalse($compiled($r));
    }

    public function test_compile_false_and_true() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $and = $f->_and([$f->_false(), $f->_true()]);
        $compiled = $and->compile();

        $this->assertFalse($compiled($n1));
        $this->assertFalse($compiled($n2));
        $this->assertFalse($compiled($r));
    }

    public function test_compile_true_and_true() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $and = $f->_and([$f->_true(), $f->_true()]);
        $compiled = $and->compile();

        $this->assertTrue($compiled($n1));
        $this->assertTrue($compiled($n2));
        $this->assertTrue($compiled($r));
    }

    public function test_compile_true_and_false_and_true() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $and = $f->_and([$f->_true(), $f->_false(), $f->_true()]);
        $compiled = $and->compile();

        $this->assertFalse($compiled($n1));
        $this->assertFalse($compiled($n2));
        $this->assertFalse($compiled($r));
    }

    public function test_and_short_circuit() {
        $p = $this->f->_true();
        $p2 = $this->f->_and([$p]);
        $this->assertSame($p, $p2);
    }

    public function test_and_empty() {
        try {
            $this->f->_and([]);
            $this->assertFalse("This should not happen!");
        }
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_compile_type_is() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r1 = $n1->add_relation("rel", [], $n2);
        $r2 = $n1->add_relation("some_type", [], $n2);

        $type_is = $this->f->_type_is("some_type");
        $compiled = $type_is->compile();

        $this->assertTrue($compiled($n1));
        $this->assertFalse($compiled($n2));
        $this->assertFalse($compiled($r1));
        $this->assertTrue($compiled($r2));
    }

    public function test_compile_property_matches() {
        $n1 = $this->g->create_node("some_type", ["foo"=>"bar"]);
        $n2 = $this->g->create_node("some_other_type", ["foo"=>"foo"]);
        $n3 = $this->g->create_node("some_other_type", ["bar"=>"foo"]);
        $r1 = $n1->add_relation("rel", ["foo"=>"foo"], $n2);
        $r2 = $n1->add_relation("some_type", ["foo"=>"bar"], $n2);
        $r3 = $n1->add_relation("some_type", [], $n2);

        $property = $this->f->_property("foo")->_matches(new Regexp("bar"));
        $compiled = $property->compile();

        $this->assertTrue($compiled($n1));
        $this->assertFalse($compiled($n2));
        $this->assertFalse($compiled($n3));
        $this->assertFalse($compiled($r1));
        $this->assertTrue($compiled($r2));
        $this->assertFalse($compiled($r3));
    }

    public function test_compile_custom() {
        $n1 = $this->g->create_node("some_type", ["foo"=>"bar"]);
        $n2 = $this->g->create_node("some_other_type", ["foo"=>"foo"]);
        $n3 = $this->g->create_node("some_other_type", ["bar"=>"foo"]);
        $r1 = $n1->add_relation("rel", ["foo"=>"foo"], $n2);
        $r2 = $n1->add_relation("some_type", ["foo"=>"bar"], $n2);
        $r3 = $n1->add_relation("some_type", [], $n2);

        $property = $this->f->_custom(function($e) {
            return $e->has_property("foo") && $e->property("foo") == "foo";
        });
        $compiled = $property->compile();

        $this->assertFalse($compiled($n1));
        $this->assertTrue($compiled($n2));
        $this->assertFalse($compiled($n3));
        $this->assertTrue($compiled($r1));
        $this->assertFalse($compiled($r2));
        $this->assertFalse($compiled($r3));
    }

    public function test_compile_not() {
        $n1 = $this->g->create_node("some_type", []);
        $r1 = $n1->add_relation("some_type", [], $n1);

        $f = $this->f;
        $true = $f->_not($f->_false())->compile();
        $false = $f->_not($f->_true())->compile();

        $this->assertTrue($true($n1));
        $this->assertTrue($true($r1));
        $this->assertFalse($false($n1));
        $this->assertFalse($false($r1));
    }


    public function test_compile_false_or_true_and_true() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $or = $f->_and([$f->_or([$f->_false(),$f->_true()]),$f->_true()]);
        $compiled = $or->compile();

        $this->assertTrue($compiled($n1));
        $this->assertTrue($compiled($n2));
        $this->assertTrue($compiled($r));
    }

    public function test_and_with_one_pred() {
        $f = $this->f;
        $true = $f->_true();
        $true2 = $f->_and([$true]);

        $this->assertSame($true, $true2);
    }

    public function test_possibly_matching_entity_types_simple() {
        $f = $this->f;
        $all_types = ["a", "b", "c"];

        $ts = $f->_true()->for_types($all_types);
        $this->assertEquals($all_types, $ts);

        $ts = $f->_false()->for_types($all_types);
        $this->assertEquals([], $ts);

        $ts = $f->_type_is("a")->for_types($all_types);
        $this->assertEquals(["a"], $ts);

        $ts = $f->_type_is("b")->for_types($all_types);
        $this->assertEquals(["b"], $ts);

        $ts = $f->_property("foo")->_matches(new Regexp("bar"))->for_types($all_types);
        $this->assertEquals($all_types, $ts);

        $ts = $f->_custom(function($e) { return false; })->for_types($all_types);
        $this->assertEquals($all_types, $ts);
    }

    public function test_possibly_matching_entity_types_not() {
        $f = $this->f;
        $all_types = ["a", "b", "c"];

        $ts = $f->_not($f->_false())->for_types($all_types);
        $this->assertEquals($all_types, $ts);

        $ts = $f->_not($f->_true())->for_types($all_types);
        $this->assertEquals($all_types, $ts);

        $ts = $f->_not($f->_type_is("a"))->for_types($all_types);
        $this->assertEquals($all_types, $ts);
    }

    public function test_possibly_matching_entity_types_and() {
        $f = $this->f;
        $all_types = ["a", "b", "c"];

        $ts = $f->_and([$f->_false(), $f->_true()])->for_types($all_types);
        $this->assertEquals([], $ts);

        $ts = $f->_and([$f->_true(), $f->_true()])->for_types($all_types);
        $this->assertEquals($all_types, $ts);

        $ts = $f->_and([$f->_type_is("a"),$f->_type_is("a")])->for_types($all_types);
        $this->assertEquals(["a"], $ts);

        $ts = $f->_and([$f->_type_is("a"),$f->_type_is("b")])->for_types($all_types);
        $this->assertEquals([], $ts);
    }

    public function test_possibly_matching_entity_types_or() {
        $f = $this->f;
        $all_types = ["a", "b", "c"];

        $ts = $f->_or([$f->_false(), $f->_true()])->for_types($all_types);
        $this->assertEquals($all_types, $ts);

        $ts = $f->_or([$f->_false(), $f->_false()])->for_types($all_types);
        $this->assertEquals([], $ts);

        $ts = $f->_or([$f->_type_is("a"),$f->_type_is("a")])->for_types($all_types);
        $this->assertEquals(["a"], $ts);

        $ts = $f->_or([$f->_type_is("a"),$f->_type_is("b")])->for_types($all_types);
        $this->assertEquals(["a", "b"], $ts);
    }

    public function test_possibly_matching_entity_types_type_is_unknown_type() {
        $f = $this->f;
        $all_types = ["a", "b", "c"];

        $ts = $f->_type_is("d")->for_types($all_types);
        $this->assertEquals([], $ts);
    }
}
