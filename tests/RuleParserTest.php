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
use Lechimp\Dicto\Rules as R;

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
        $this->parser->which_expression = "variable";
        $res = $this->parse("{Classes, Functions}");

        $expected = new V\Any(array
            ( new V\Classes()
            , new V\Functions()
            ));
        $this->assertEquals($expected, $res);
    }

    public function test_except() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("Classes except Functions");

        $expected = new V\Except
            ( new V\Classes()
            , new V\Functions()
            );
        $this->assertEquals($expected, $res);
    }

    public function test_any_except() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("{Classes except Functions, Methods} except Globals");

        $expected = new V\Except
            ( new V\Any(array
                ( new V\Except
                    ( new V\Classes()
                    , new V\Functions()
                    )
                , new V\Methods()
                ))
            , new V\Globals()
            );
        $this->assertEquals($expected, $res);
    }

    public function test_except_binding() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("Classes except Functions except Methods");

        $expected = new V\Except
            ( new V\Except
                ( new V\Classes()
                , new V\Functions()
                )
            , new V\Methods()
            );
        $this->assertEquals($expected, $res);
    }

    public function test_with_name() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("Classes with name:\"foo\"");

        $expected = new V\WithName
            ( "foo"
            , new V\Classes()
            );
        $this->assertEquals($expected, $res);
    }

    public function test_string() {
        $this->parser->which_expression = "string";
        $res = $this->parse("\"foo\"");

        $this->assertEquals("foo", $res);
    }

    public function test_string_escaped_quote() {
        $this->parser->which_expression = "string";
        $res = $this->parse("\"foo\\\"\"");

        $this->assertEquals("foo\"", $res);
    }

    public function test_string_escaped_newline() {
        $this->parser->which_expression = "string";
        $res = $this->parse("\"foo\\n\"");

        $this->assertEquals("foo\n", $res);
    }

    public function test_classes_cannot_contain_text() {
        $res = $this->parser->parse("Classes cannot contain text \"foo\"");

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_CANNOT
                , new V\Classes()
                , new R\ContainText
                , array("foo")
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_classes_must_contain_text() {
        $res = $this->parser->parse("Classes must contain text \"foo\"");

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_MUST
                , new V\Classes()
                , new R\ContainText
                , array("foo")
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_only_classes_can_contain_text() {
        $res = $this->parser->parse("only Classes can contain text \"foo\"");

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_ONLY_CAN
                , new V\Classes()
                , new R\ContainText
                , array("foo")
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_classes_with_name_must_contain_text() {
        $res = $this->parser->parse("Classes with name:\"foo\" must contain text \"foo\"");

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_MUST
                , new V\WithName
                    ( "foo"
                    , new V\Classes()
                    )
                , new R\ContainText
                , array("foo")
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_classes_and_functions_must_contain_text() {
        $res = $this->parser->parse("{Classes,Functions} must contain text \"foo\"");

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_MUST
                , new V\Any(array
                    ( new V\Classes()
                    , new V\Functions()
                    ))
                , new R\ContainText
                , array("foo")
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_classes_cannot_depend_on_functions() {
        $res = $this->parser->parse("Classes cannot depend on Functions");

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_CANNOT
                , new V\Classes()
                , new R\DependOn
                , array(new V\Functions())
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_classes_cannot_invoke_functions() {
        $res = $this->parser->parse("Classes cannot invoke Functions");

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_CANNOT
                , new V\Classes()
                , new R\Invoke
                , array(new V\Functions())
                )
            );
        $this->assertEquals($expected, $res->rules());
    }
}
