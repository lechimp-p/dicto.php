<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto as Dicto;
use Lechimp\Dicto\Dicto as D;
use Lechimp\Dicto\Variables as V;
use Lechimp\Dicto\Definition\RuleParser;

class PPrintRuleTest extends PHPUnit_Framework_TestCase {
    protected static $printed_rules;

    static public function setUpBeforeClass() {
        $parser = new RuleParser
            ( array
                ( new V\Classes()
                , new V\Functions()
                , new V\Globals()
                , new V\Files()
                , new V\Methods()
                // TODO: Add some language constructs here...
                )
            );

        $rules = <<<RULES

A = Classes
B = Classes

A must depend on B
A cannot depend on B
only A can depend on B
A must invoke B
A cannot invoke B
only A can invoke B
A must contain text "foo"
A cannot contain text "foo"
only A can contain text "foo"

RULES;
        $ruleset = $parser->parse($rules);

        self::$printed_rules = array_map(function($rule) {
            return $rule->pprint();
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
