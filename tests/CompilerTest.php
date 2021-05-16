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
use Lechimp\Dicto\Definition\Compiler;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Variables as V;
use Lechimp\Dicto\Rules as R;

class _Compiler extends Compiler {
    public function _compile_definition($node) {
        return $this->compile_definition($node);
    }
    public function _add_predefined_variables() {
        $this->add_predefined_variables();
    }
}

class CompilerTest extends \PHPUnit\Framework\TestCase {
    public function setUp() : void {
        $this->f = new AST\Factory();
        $this->compiler = new _Compiler
            ( array
                ( new V\Namespaces()
                , new V\Classes()
                , new V\Interfaces()
                , new V\Traits()
                , new V\Functions()
                , new V\Globals()
                , new V\Files()
                , new V\Methods()
                , new V\ErrorSuppressor()
                , new V\ExitOrDie()
                )
            , array
                ( new R\ContainText()
                )
            , array
                ( new V\Name()
                , new V\In()
                )
            );
    }

    public function compile($ast) {
        return $this->compiler->compile($ast);
    }

    public function test_empty() {
        $res = $this->compile($this->f->root([]));
        $this->assertEquals(new Ruleset(array(), array()), $res);
    }

    public function test_variable() {
        $res = $this->compile
            ( $this->f->root
                ([ $this->f->assignment
                    ( $this->f->name("AllClasses")
                    , $this->f->name("Classes")
                    )
                ])
            );

        $expected = array
            ( "AllClasses" => new V\Classes("AllClasses")
            );

        $this->assertEquals($expected, $res->variables());
    }

    public function test_error_suppressor() {
        $res = $this->compile
            ( $this->f->root
                ([ $this->f->assignment
                    ( $this->f->name("TheErrorSuppressor")
                    , $this->f->name("ErrorSuppressor")
                    )
                ])
            );

        $expected = array
            ( "TheErrorSuppressor" => new V\ErrorSuppressor("TheErrorSuppressor")
            );

        $this->assertEquals($expected, $res->variables());
    }

    public function test_interface_variable() {
        $res = $this->compile
            ( $this->f->root
                ([ $this->f->assignment
                    ( $this->f->name("AllInterfaces")
                    , $this->f->name("Interfaces")
                    )
                ])
            );

        $expected = array
            ( "AllInterfaces" => new V\Interfaces("AllInterfaces")
            );

        $this->assertEquals($expected, $res->variables());
    }

    public function test_trait_variable() {
        $res = $this->compile
            ( $this->f->root
                ([ $this->f->assignment
                    ( $this->f->name("AllTraits")
                    , $this->f->name("Traits")
                    )
                ])
            );

        $expected = array
            ( "AllTraits" => new V\Traits("AllTraits")
            );

        $this->assertEquals($expected, $res->variables());
    }

    public function test_namespace_variable() {
        $res = $this->compile
            ( $this->f->root
                ([ $this->f->assignment
                    ( $this->f->name("AllNamespaces")
                    , $this->f->name("Namespaces")
                    )
                ])
            );

        $expected = array
            ( "AllNamespaces" => new V\Namespaces("AllNamespaces")
            );

        $this->assertEquals($expected, $res->variables());
    }

    public function test_classes_in_namespaces_with_name_1() {
        $this->compiler->_add_predefined_variables();
        $res = $this->compiler->_compile_definition
            ( $this->f->property
                ( $this->f->name("Classes")
                , $this->f->atom("in")
                , [ $this->f->property
                    ( $this->f->name("Namespaces")
                    , $this->f->atom("with name")
                    , [ $this->f->string_value("foo.*") ]
                    )]
                )
            );

        $expected = new V\WithProperty
            ( new V\Classes
            , new V\In
            , array
                ( new V\WithProperty
                    ( new V\Namespaces
                    , new V\Name
                    , array(new Regexp("foo.*"))
                    )
                )
            );

        $this->assertEquals($expected, $res);
    }

    public function test_classes_in_namespaces_with_name_2() {
        $this->compiler->_add_predefined_variables();
        $res = $this->compiler->_compile_definition
            ( $this->f->property
                ( $this->f->property
                    ( $this->f->name("Classes")
                    , $this->f->atom("in")
                    , [ $this->f->name("Namespaces") ]
                    )
                , $this->f->atom("with name")
                , [ $this->f->string_value("foo.*") ]
                )
            );

        $expected = new V\WithProperty
            ( new V\WithProperty
                ( new V\Classes
                , new V\In
                , array(new V\Namespaces)
                )
            , new V\Name
            , array(new Regexp("foo.*"))
            );

        $this->assertEquals($expected, $res);
    }

    public function test_variables() {
        $res = $this->compile
            ( $this->f->root
                ([$this->f->assignment
                    ( $this->f->name("AllClasses")
                    , $this->f->name("Classes")
                    )
                , $this->f->assignment
                    ( $this->f->name("AllFunctions")
                    , $this->f->name("Functions")
                    )
                ])
            );

        $expected = array
            ( "AllClasses" => new V\Classes("AllClasses")
            , "AllFunctions" => new V\Functions("AllFunctions")
            );

        $this->assertEquals($expected, $res->variables());
    }

    public function test_any_short_circuit() {
        $this->compiler->_add_predefined_variables();
        $res = $this->compiler->_compile_definition
            ( $this->f->any
                ( [$this->f->name("Classes")] )
            );

        $expected = new V\Classes();
        $this->assertEquals($expected, $res);
    }

    public function test_any() {
        $this->compiler->_add_predefined_variables();
        $res = $this->compiler->_compile_definition
            ( $this->f->any
                ([$this->f->name("Classes")
                , $this->f->name("Functions")
                ])
            );

        $expected = new V\Any(array
            ( new V\Classes()
            , new V\Functions()
            ));
        $this->assertEquals($expected, $res);
    }

    public function test_except() {
        $this->compiler->_add_predefined_variables();
        $res = $this->compiler->_compile_definition
            ( $this->f->except
                ( $this->f->name("Classes")
                , $this->f->name("Functions")
                )
            );

        $expected = new V\Except
            ( new V\Classes()
            , new V\Functions()
            );
        $this->assertEquals($expected, $res);
    }

    public function test_any_except() {
        $this->compiler->_add_predefined_variables();
        $res = $this->compiler->_compile_definition
            ( $this->f->except
                ( $this->f->any
                    ([$this->f->except
                        ( $this->f->name("Classes")
                        , $this->f->name("Functions")
                        )
                    , $this->f->name("Methods")
                    ])
                , $this->f->name("Globals")
                )
            );

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

    public function test_with_name() {
        $this->compiler->_add_predefined_variables();
        $res = $this->compiler->_compile_definition
            ( $this->f->property
                ( $this->f->name("Classes")
                , $this->f->atom("with name")
                , [$this->f->string_value("foo")]
                )
            );

        $expected = new V\WithProperty
            ( new V\Classes()
            , new V\Name
            , array(new Regexp("foo"))
            );
        $this->assertEquals($expected, $res);
    }

    public function test_with_name_assignment() {
        $res = $this->compile
            ( $this->f->root
                ([$this->f->assignment
                    ( $this->f->name("Foo")
                    , $this->f->property
                        ( $this->f->name("Classes")
                        , $this->f->atom("with name")
                        , [$this->f->string_value("foo")]
                        )
                    )
                ])
            );

        $expected = array
            ( "Foo" => (new V\WithProperty
                ( new V\Classes()
                , new V\Name
                , array(new Regexp("foo"))
                ))
                ->withName("Foo")
            );
        $this->assertEquals($expected, $res->variables());

    }

    public function test_methods_in_classes() {
        $this->compiler->_add_predefined_variables();
        $res = $this->compiler->_compile_definition
            ( $this->f->property
                ( $this->f->name("Methods")
                , $this->f->atom("in")
                , [$this->f->name("Classes")]
                )
            );

        $expected = new V\WithProperty
            ( new V\Methods()
            , new V\In
            , array(new V\Classes())
            );
        $this->assertEquals($expected, $res);
    }

    public function test_exit_or_die() {
        $this->compiler->_add_predefined_variables();
        $res = $this->compiler->_compile_definition
            ( $this->f->name("ExitOrDie")
            );

        $expected = new V\ExitOrDie();
        $this->assertEquals($expected, $res);
    }

    public function test_classes_cannot_contain_text() {
        $res = $this->compile
            ( $this->f->root
                ([$this->f->rule
                    ( $this->f->cannot()
                    , $this->f->property
                        ( $this->f->name("Classes")
                        , $this->f->atom("contain text")
                        , [$this->f->string_value("foo")]
                        )
                    )
                ])
            );

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_CANNOT
                , new V\Classes()
                , new R\ContainText
                , array(new Regexp("foo"))
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_classes_must_contain_text() {
        $res = $this->compile
            ( $this->f->root
                ([$this->f->rule
                    ( $this->f->must()
                    , $this->f->property
                        ( $this->f->name("Classes")
                        , $this->f->atom("contain text")
                        , [$this->f->string_value("foo")]
                        )
                    )
                ])
            );

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_MUST
                , new V\Classes()
                , new R\ContainText
                , array(new Regexp("foo"))
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_only_classes_can_contain_text() {
        $res = $this->compile
            ( $this->f->root
                ([$this->f->rule
                    ( $this->f->only_X_can()
                    , $this->f->property
                        ( $this->f->name("Classes")
                        , $this->f->atom("contain text")
                        , [$this->f->string_value("foo")]
                        )
                    )
                ])
            );

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_ONLY_CAN
                , new V\Classes()
                , new R\ContainText
                , array(new Regexp("foo"))
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_only_classes_and_methods_can_contain_text() {
        $res = $this->compile
            ( $this->f->root
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
                ])
            );

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_ONLY_CAN
                , new V\Any(array
                    ( new V\Classes()
                    , new V\Methods()
                    ))
                , new R\ContainText
                , array(new Regexp("foo"))
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_only_classes_and_methods_in_classes_can_contain_text() {
        $res = $this->compile
            ( $this->f->root
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
                ])
            );

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_ONLY_CAN
                , new V\Any(array
                    ( new V\Classes()
                    , new V\WithProperty
                        ( new V\Methods()
                        , new V\In()
                        , array(new V\Classes())
                        )
                    ))
                , new R\ContainText
                , array(new Regexp("foo"))
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    public function test_explain_variables() {
        $res = $this->compile
            ( $this->f->root
                ([$this->f->explanation("Explanation")
                , $this->f->assignment
                    ( $this->f->name("AllClasses")
                    , $this->f->name("Classes")
                    )
                ])
            );

        $res = $res->variables()["AllClasses"];
        $this->assertInstanceOf(V\Classes::class, $res);
        $this->assertEquals("Explanation", $res->explanation());
    }

    public function test_explain_rules() {
        $res = $this->compile
            ( $this->f->root
                ([$this->f->explanation("Explanation")
                , $this->f->rule
                    ( $this->f->cannot()
                    , $this->f->property
                        ( $this->f->name("Classes")
                        , $this->f->atom("contain text")
                        , [$this->f->string_value("name")]
                        )
                    )
                ])
            );

        $res = $res->rules()[0];
        $this->assertInstanceOf(R\Rule::class, $res);
        $this->assertEquals("Explanation", $res->explanation());
    }

    public function test_no_double_explanation() {
        $res = $this->compile
            ( $this->f->root
                ([$this->f->explanation("Explanation")
                , $this->f->assignment
                    ( $this->f->name("AllClasses")
                    , $this->f->name("Classes")
                    )
                , $this->f->assignment
                    ( $this->f->name("AllFunctions")
                    , $this->f->name("Functions")
                    )
                ])
            );

        $res = $res->variables()["AllFunctions"];
        $this->assertInstanceOf(V\Functions::class, $res);
        $this->assertSame(null, $res->explanation());
    }
}
