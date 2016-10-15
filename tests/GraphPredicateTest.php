<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

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
             , $f->_property("bar")->_matches(".*")
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

    public function test_compile_true_and_false() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $or = $f->_and([$f->_true(), $f->_false()]);
        $compiled = $or->compile();

        $this->assertFalse($compiled($n1));
        $this->assertFalse($compiled($n2));
        $this->assertFalse($compiled($r));
    }

    public function test_compile_false_and_true() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $or = $f->_and([$f->_false(), $f->_true()]);
        $compiled = $or->compile();

        $this->assertFalse($compiled($n1));
        $this->assertFalse($compiled($n2));
        $this->assertFalse($compiled($r));
    }

    public function test_compile_true_and_true() {
        $n1 = $this->g->create_node("some_type", []);
        $n2 = $this->g->create_node("some_other_type", []);
        $r = $n1->add_relation("rel", [], $n2);

        $f = $this->f;
        $or = $f->_and([$f->_true(), $f->_true()]);
        $compiled = $or->compile();

        $this->assertTrue($compiled($n1));
        $this->assertTrue($compiled($n2));
        $this->assertTrue($compiled($r));
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
}
