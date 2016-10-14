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

class GraphPredicateTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->f = new PredicateFactory();
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
        $this->assertInstanceOf(Lechimp\Dicto\Graph\Predicate::class, $pred);
    }
}
