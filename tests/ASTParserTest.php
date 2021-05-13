<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Regexp;
use Lechimp\Dicto\Definition\AST;
use Lechimp\Dicto\Definition\ASTParser;

class _ASTParser extends ASTParser {
    // Makes testing easier.
    public $which_expression = "root";
    public function root() {
        $which = $this->which_expression;
        return parent::$which();
    }
}

class ASTParserTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->f = new AST\Factory();
        $this->parser = new _ASTParser($this->f);
    }

    public function parse($expr) {
        return $this->parser->parse($expr);
    }

    public function test_empty() {
        $res = $this->parse("");
        $this->assertEquals($this->f->root([]), $res);
    }

    public function test_variable() {
        $res = $this->parse("AllClasses = Classes");

        $expected = $this->f->root
            ([$this->f->assignment
                ( $this->f->name("AllClasses")
                , $this->f->name("Classes")
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_single_char_variables() {
        $res = $this->parse("A = B");

        $expected = $this->f->root
            ([$this->f->assignment
                ( $this->f->name("A")
                , $this->f->name("B")
                )
            ]);
    }

    public function test_classes_in_namespaces_with_name_1() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("Classes in: Namespaces with name: \"foo.*\"");

        $expected = $this->f->property
            ( $this->f->name("Classes")
            , $this->f->atom("in")
            , [$this->f->property
                ( $this->f->name("Namespaces")
                , $this->f->atom("with name")
                , [$this->f->string_value("foo.*")]
                )]
            );

        $this->assertEquals($expected, $res);
    }

    public function test_classes_in_namespaces_with_name_2() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("{Classes in: Namespaces} with name: \"foo.*\"");

        $expected = $this->f->property
            ( $this->f->any
                ([$this->f->property
                    ( $this->f->name("Classes")
                    , $this->f->atom("in")
                    , [$this->f->name("Namespaces")]
                    )
                ])
            , $this->f->atom("with name")
            , [$this->f->string_value("foo.*")]
            );

        $this->assertEquals($expected, $res);
    }

    public function test_variables() {
        $res = $this->parse("AllClasses = Classes\nAllFunctions = Functions");

        $expected = $this->f->root
            ([$this->f->assignment
                ( $this->f->name("AllClasses")
                , $this->f->name("Classes")
                )
            , $this->f->assignment
                ( $this->f->name("AllFunctions")
                , $this->f->name("Functions")
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_name() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("Classes");

        $expected = $this->f->name("Classes");

        $this->assertEquals($expected, $res);
    }

    public function test_any() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("{Classes, Functions}");

        $expected = $this->f->any
            ([$this->f->name("Classes")
            , $this->f->name("Functions")
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_except() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("Classes except Functions");

        $expected = $this->f->except
            ( $this->f->name("Classes")
            , $this->f->name("Functions")
            );

        $this->assertEquals($expected, $res);
    }

    public function test_any_except() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("{Classes except Functions, Methods} except Globals");

        $expected = $this->f->except
            ( $this->f->any
                ([$this->f->except
                    ( $this->f->name("Classes")
                    , $this->f->name("Functions")
                    )
                , $this->f->name("Methods")
                ])
            , $this->f->name("Globals")
            );

        $this->assertEquals($expected, $res);
    }

    public function test_except_binding() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("Classes except Functions except Methods");

        $expected = $this->f->except
            ( $this->f->except
                ( $this->f->name("Classes")
                , $this->f->name("Functions")
                )
            , $this->f->name("Methods")
            );

        $this->assertEquals($expected, $res);
    }

    public function test_with_name() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("Classes with name:\"foo\"");

        $expected = $this->f->property
            ( $this->f->name("Classes")
            , $this->f->atom("with name")
            , [$this->f->string_value("foo")]
            );

        $this->assertEquals($expected, $res);
    }

    public function test_with_name_assignment() {
        $res = $this->parse("Foo = Classes with name: \"foo\"");

        $expected = $this->f->root
            ([$this->f->assignment
                ( $this->f->name("Foo")
                , $this->f->property
                    ( $this->f->name("Classes")
                    , $this->f->atom("with name")
                    , [$this->f->string_value("foo")]
                    )
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_methods_in_classes() {
        $this->parser->which_expression = "variable";
        $res = $this->parse("Methods in: Classes");

        $expected = $this->f->property
            ( $this->f->name("Methods")
            , $this->f->atom("in")
            , [$this->f->name("Classes")]
            );

        $this->assertEquals($expected, $res);
    }

    public function test_string() {
        $this->parser->which_expression = "string";
        $res = $this->parse("\"foo\"");

        $this->assertEquals($this->f->string_value("foo"), $res);
    }

    public function test_string_escaped_quote() {
        $this->parser->which_expression = "string";
        $res = $this->parse("\"foo\\\"\"");

        $this->assertEquals($this->f->string_value("foo\""), $res);
    }

    public function test_string_escaped_newline() {
        $this->parser->which_expression = "string";
        $res = $this->parse("\"foo\\n\"");

        $this->assertEquals($this->f->string_value("foo\n"), $res);
    }

    public function test_string_with_star_and_dot() {
        $this->parser->which_expression = "string";
        $res = $this->parse("\"A.*\"");

        $this->assertEquals($this->f->string_value("A.*"), $res);
    }

    public function test_classes_cannot_contain_text() {
        $res = $this->parser->parse("Classes cannot contain text: \"foo\"");

        $expected = $this->f->root
            ([$this->f->rule
                ( $this->f->cannot()
                , $this->f->property
                    ( $this->f->name("Classes")
                    , $this->f->atom("contain text")
                    , [$this->f->string_value("foo")]
                    )
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_classes_must_contain_text() {
        $res = $this->parser->parse("Classes must contain text: \"foo\"");

        $expected = $this->f->root
            ([$this->f->rule
                ( $this->f->must()
                , $this->f->property
                    ( $this->f->name("Classes")
                    , $this->f->atom("contain text")
                    , [$this->f->string_value("foo")]
                    )
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_only_classes_can_contain_text() {
        $res = $this->parser->parse("only Classes can contain text: \"foo\"");

        $expected = $this->f->root
            ([$this->f->rule
                ( $this->f->only_X_can()
                , $this->f->property
                    ( $this->f->name("Classes")
                    , $this->f->atom("contain text")
                    , [$this->f->string_value("foo")]
                    )
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_only_classes_and_methods_can_contain_text() {
        $res = $this->parser->parse("only {Classes, Methods} can contain text: \"foo\"");

        $expected = $this->f->root
            ([$this->f->rule
                ( $this->f->only_X_can()
                , $this->f->property
                    ( $this->f->any
                        ([$this->f->name("Classes")
                        , $this->f->name("Methods")
                        ])
                    , $this->f->atom("contain text")
                    , [$this->f->string_value("foo")]
                    )
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_only_classes_and_methods_in_classes_can_contain_text() {
        $res = $this->parser->parse("only {Classes, Methods in: Classes} can contain text: \"foo\"");

        $expected = $this->f->root
            ([$this->f->rule
                ( $this->f->only_X_can()
                , $this->f->property
                    ( $this->f->any
                        ([$this->f->name("Classes")
                        , $this->f->property
                            ( $this->f->name("Methods")
                            , $this->f->atom("in")
                            , [$this->f->name("Classes")]
                            )
                        ])
                    , $this->f->atom("contain text")
                    , [$this->f->string_value("foo")]
                    )
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_classes_with_name_must_contain_text() {
        $res = $this->parser->parse("Classes with name: \"foo\" must contain text: \"foo\"");


        $expected = $this->f->root
            ([$this->f->rule
                ( $this->f->must()
                , $this->f->property
                    ( $this->f->property
                        ( $this->f->name("Classes")
                        , $this->f->atom("with name")
                        , [$this->f->string_value("foo")]
                        )
                    , $this->f->atom("contain text")
                    , [$this->f->string_value("foo")]
                    )
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_classes_and_functions_must_contain_text() {
        $res = $this->parser->parse("{Classes,Functions} must contain text: \"foo\"");

        $expected = $this->f->root
            ([$this->f->rule
                ( $this->f->must()
                , $this->f->property
                    ( $this->f->any
                        ([$this->f->name("Classes")
                        , $this->f->name("Functions")
                        ])
                    , $this->f->atom("contain text")
                    , [$this->f->string_value("foo")]
                    )
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_drop_empty_lines() {
        $res = $this->parse("\nAllClasses = Classes\n\nAllFunctions = Functions\n");

        $expected = $this->f->root
            ([$this->f->assignment
                ( $this->f->name("AllClasses")
                , $this->f->name("Classes")
                )
            , $this->f->assignment
                ( $this->f->name("AllFunctions")
                , $this->f->name("Functions")
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_explain_variables() {
        $res = $this->parse("/** Explanation */\nAllClasses = Classes");

        $expected = $this->f->root
            ([$this->f->explanation("Explanation")
            , $this->f->assignment
                ( $this->f->name("AllClasses")
                , $this->f->name("Classes")
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_explain_multi_line () {
        $res = $this->parse("/** Explanation \n * some more \n */\n\nAllClasses = Classes");

        $expected = $this->f->root
            ([$this->f->explanation("Explanation\nsome more")
            , $this->f->assignment
                ( $this->f->name("AllClasses")
                , $this->f->name("Classes")
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_no_explanation_ordinary_comment() {
        $res = $this->parse("// Comment \nAllClasses = Classes");

        $expected = $this->f->root
            ([$this->f->assignment
                ( $this->f->name("AllClasses")
                , $this->f->name("Classes")
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_no_explanation_ordinary_comment_multi_line() {
        $res = $this->parse("/* Comment \nfoo\n*/\nAllClasses = Classes");

        $expected = $this->f->root
            ([$this->f->assignment
                ( $this->f->name("AllClasses")
                , $this->f->name("Classes")
                )
            ]);

        $this->assertEquals($expected, $res);
    }

    public function test_end_comment() {
        $res = $this->parse("\nAllClasses = Classes\n\nAllFunctions = Functions\n// Comment\n/* Comment */");

        $expected = $this->f->root
            ([$this->f->assignment
                ( $this->f->name("AllClasses")
                , $this->f->name("Classes")
                )
            , $this->f->assignment
                ( $this->f->name("AllFunctions")
                , $this->f->name("Functions")
                )
            ]);

        $this->assertEquals($expected, $res);
    }
}
