<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Definition\RuleParser;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Variables as V;

class _RuleParser extends RuleParser {
    // Makes testing easier.
    public $which_expression = "root";
    public function root() {
        $which = $this->which_expression;
        return parent::$which();
    }
}

class RuleParserTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->parser = new _RuleParser();
    }

    public function parse($expr) {
        return $this->parser->parse($expr);
    }

    public function test_empty() {
        $res = $this->parse("");
        $this->assertEquals(new Ruleset(array(), array()), $res);
    }

    public function test_variable() {
        $res = $this->parse("AllClasses = Classes");

        $expected = array
            ( "AllClasses" => new V\Classes("AllClasses")
            );

        $this->assertEquals($expected, $res->variables());
    }

    public function test_variables() {
        $res = $this->parse("AllClasses = Classes\nAllFunctions = Functions");

        $expected = array
            ( "AllClasses" => new V\Classes("AllClasses")
            , "AllFunctions" => new V\Functions("AllFunctions")
            );

        $this->assertEquals($expected, $res->variables());
    }

    public function test_any() {
        $this->parser->which_expression = "variable_definition";
        $res = $this->parse("{Classes, Functions}");

        $expected = new V\Any(array
            ( new V\Classes()
            , new V\Functions()
            ));
        $this->assertEquals($expected, $res);
    }
}
