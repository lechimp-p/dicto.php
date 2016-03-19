<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Dicto as Dicto;
use Lechimp\Dicto\Dicto as D;

class PPrintRuleTest extends PHPUnit_Framework_TestCase {
    protected static $printed_rules;

    static public function setUpBeforeClass() {
        D::startDefinition();

        D::A()->means()->classes();
        D::B()->means()->classes();

        D::A()->must()->depend_on()->B();
        D::A()->cannot()->depend_on()->B();
        D::only()->A()->can()->depend_on()->B();
        D::A()->must()->invoke()->B();
        D::A()->cannot()->invoke()->B();
        D::only()->A()->can()->invoke()->B();
        D::A()->must()->contain_text("foo");
        D::A()->cannot()->contain_text("foo");
        D::only()->A()->can()->contain_text("foo");

        $ruleset = D::endDefinition();

        $pprinter = new Dicto\Output\RulePrinter;

        self::$printed_rules = array_map(function($rule) use ($pprinter) {
            return $pprinter->pprint($rule);
        }, $ruleset->rules());
    }

    public function test_must_depend_on() {
        $this->assertEquals("A must depend on B", self::$printed_rules[0]);
    }

    public function test_cannot_depend_on() {
        $this->assertEquals("A cannot depend on B", self::$printed_rules[1]);
    }

    public function test_only_can_depend_on() {
        $this->assertEquals("only A can depend on B", self::$printed_rules[2]);
    }

    public function test_must_invoke() {
        $this->assertEquals("A must invoke B", self::$printed_rules[3]);
    }

    public function test_cannot_invoke() {
        $this->assertEquals("A cannot invoke B", self::$printed_rules[4]);
    }

    public function test_only_can_invoke() {
        $this->assertEquals("only A can invoke B", self::$printed_rules[5]);
    }

    public function test_must_contain_text() {
        $this->assertEquals("A must contain text \"foo\"", self::$printed_rules[6]);
    }

    public function test_cannot_contain_text() {
        $this->assertEquals("A cannot contain text \"foo\"", self::$printed_rules[7]);
    }

    public function test_only_can_contain_text() {
        $this->assertEquals("only A can contain text \"foo\"", self::$printed_rules[8]);
    }
}
