<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Verification as Ver;

require_once(__DIR__."/ArtifactSelectionTest.php");

class ArtifactVerificationTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $selector = new Ver\Implementation\Selector;
        $this->verifier = new Ver\Implementation\Verifier($selector);
    }

    public function tearDown() {
        Dicto::discardDefinition();
    }

    public function get_rule($definition) {
        Dicto::startDefinition();
        $definition();
        $defs = Dicto::endDefinition();
        $rules = $defs->rules();

        $this->assertCount(1, $rules);
        return $rules[0]; 
    }

    /**
     * @dataProvider has_subject_test_provider
     */
    public function test_has_subject($def, Ver\Artifact $artifact, $has_subject) {
        $rule = $this->get_rule($def);
        $res = $this->verifier->has_subject($rule, $artifact);
        $this->assertSame($has_subject, $res); 
    }

    public function has_subject_test_provider() {
        $std_def = function() {
            Dicto::allClasses()->means()->classes();
            Dicto::anyFunction()->means()->functions();
            Dicto::namedClasses()->means()->classes()->with()->name("Foo.*");
        };
        return array
            (array
                ( function() use ($std_def) { 
                    $std_def();
                    Dicto::allClasses()->cannot()->invoke()->anyFunction();    
                }
                , new ClassMock("AClass")
                , true
                )
            , array
                ( function() use ($std_def) { 
                    $std_def();
                    Dicto::allClasses()->cannot()->depend_on()->namedClasses();    
                }
                , new ClassMock("AClass")
                , true
                )
            , array
                ( function() use ($std_def) { 
                    $std_def();
                    Dicto::allClasses()->must()->invoke()->anyFunction();    
                }
                , new ClassMock("AClass")
                , true
                )
            , array
                ( function() use ($std_def) { 
                    $std_def();
                    Dicto::namedClasses()->cannot()->invoke()->anyFunction();    
                }
                , new ClassMock("AClass")
                , false
                )
            , array
                ( function() use ($std_def) { 
                    $std_def();
                    Dicto::allClasses()->cannot()->invoke()->anyFunction();    
                }
                , new FunctionMock("a_function")
                , false
                )
            , array
                ( function() use ($std_def) { 
                    $std_def();
                    Dicto::allClasses()->cannot()->invoke()->anyFunction();    
                }
                , new GlobalMock("a_global")
                , false
                )
            , array
                ( function() use ($std_def) { 
                    $std_def();
                    Dicto::allClasses()->cannot()->invoke()->anyFunction();    
                }
                , new BuildinMock("a_buildin")
                , false
                )
            , array
                ( function() use ($std_def) { 
                    $std_def();
                    Dicto::allClasses()->cannot()->invoke()->anyFunction();    
                }
                , new FileMock("a_file")
                , false
                )
            , array
                // With only, the subjects really are all classes that are
                // not contained in the variable.
                ( function() use ($std_def) { 
                    $std_def();
                    Dicto::only()->namedClasses()->can()->invoke()->anyFunction();    
                }
                , new ClassMock("AClass")
                , true
                )
            , array
                ( function() use ($std_def) { 
                    $std_def();
                    Dicto::only()->namedClasses()->can()->invoke()->anyFunction();    
                }
                , new ClassMock("FooClass")
                , false
                )
            );
    }

    // DEPEND ON

    public function get_violations($def, $artifact) {
        $rule = $this->get_rule($def);
        return $this->verifier->violations_in($rule, $artifact);
    }

    public function get_violations_and_rule($def, $artifact) {
        $rule = $this->get_rule($def);
        return array
            ( $this->verifier->violations_in($rule, $artifact)
            , $rule
            );
    }


    public function test_depend_on_1() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("a_function");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep, $fun_dep));

        $def = function() {
            Dicto::allClasses()->means()->classes();
            Dicto::allClasses()->cannot()->depend_on()->allClasses();
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $cls);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($cls, $violation->artifact());
        $this->assertSame($cls_dep, $violation->violator());
    }

    public function test_depend_on_2() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("a_function");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep, $fun_dep));

        $def = function () {
            Dicto::BClasses()->means()->classes()->with()->name("B.*");
            Dicto::BClasses()->cannot()->depend_on()->BClasses();
        };
        $violations = $this->get_violations($def, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_depend_on_3() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("a_function");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep, $fun_dep));

        $def = function () {
            Dicto::AClasses()->means()->classes()->with()->name("A.*");
            Dicto::AClasses()->cannot()->depend_on()->AClasses();
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $cls);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($cls, $violation->artifact());
        $this->assertSame($cls_dep, $violation->violator());
    }

    public function test_depend_on_4() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("a_function");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep, $fun_dep));

        $def = function () { 
            Dicto::AClasses()->means()->classes()->with()->name("A.*");
            Dicto::AFunctions()->means()->functions()->with()->name("a_*");
            Dicto::AClasses()->cannot()->depend_on()->AFunctions();
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $cls);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($cls, $violation->artifact());
        $this->assertSame($fun_dep, $violation->violator());
    }

    public function test_depend_on_5() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("a_function");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep, $fun_dep));

        $def = function () { 
            Dicto::AllClasses()->means()->classes();
            Dicto::AFunctions()->means()->functions()->with()->name("a_*");
            Dicto::AllClasses()->must()->depend_on()->AFunctions();
        };
        $violations = $this->get_violations($def, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_depend_on_6() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep));

        $def = function () { 
            Dicto::AllClasses()->means()->classes();
            Dicto::AFunctions()->means()->functions()->with()->name("a_*");
            Dicto::AllClasses()->must()->depend_on()->AFunctions();
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $cls);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($cls, $violation->artifact());
        $this->assertSame($cls, $violation->violator());
    }

    public function test_depend_on_7() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("a_function");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep, $fun_dep));

        $def = function () { 
            Dicto::AClasses()->means()->classes()->with()->name("A.*");
            Dicto::AFunctions()->means()->functions()->with()->name("a_*");
            Dicto::only()->AClasses()->can()->depend_on()->AFunctions();
        };
        $violations = $this->get_violations($def, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_depend_on_8() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("a_function");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep, $fun_dep));

        $def = function () { 
            Dicto::BClasses()->means()->classes()->with()->name("B.*");
            Dicto::AFunctions()->means()->functions()->with()->name("a_*");
            Dicto::only()->BClasses()->can()->depend_on()->AFunctions();
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $cls);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($cls, $violation->artifact());
        $this->assertSame($fun_dep, $violation->violator());
    }

    public function test_depend_on_9() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("b_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun_dep), array($fun_dep));

        $def = function () { 
            Dicto::AllFunctions()->means()->functions();
            Dicto::Silencer()->means()->buildins()->with()->name("@");
            Dicto::AllFunctions()->cannot()->depend_on()->Silencer();
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $fun);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($fun, $violation->artifact());
        $this->assertSame($bldin_dep, $violation->violator());
    }

    // Invoke

    public function test_invoke_1() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun2_dep));

        $def = function () { 
            Dicto::AllFunctions()->means()->functions();
            Dicto::CFunction()->means()->functions()->with()->name("c_function");
            Dicto::AllFunctions()->cannot()->invoke()->CFunction();
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $fun);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($fun, $violation->artifact());
        $this->assertSame($fun2_dep, $violation->violator());
    }

    public function test_invoke_2() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun2_dep));

        $def = function () { 
            Dicto::AllFunctions()->means()->functions();
            Dicto::BFunction()->means()->functions()->with()->name("b_function");
            Dicto::AllFunctions()->cannot()->invoke()->BFunction();
        };
        $violations = $this->get_violations($def, $fun);

        $this->assertCount(0, $violations);
    }

    public function test_invoke_3() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun2_dep));

        $def = function () { 
            Dicto::AllFunctions()->means()->functions();
            Dicto::CFunction()->means()->functions()->with()->name("c_function");
            Dicto::AllFunctions()->must()->invoke()->CFunction();
        };
        $violations = $this->get_violations($def, $fun);

        $this->assertCount(0, $violations);
    }

    public function test_invoke_4() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun2_dep));

        $def = function () { 
            Dicto::AllFunctions()->means()->functions();
            Dicto::BFunction()->means()->functions()->with()->name("b_function");
            Dicto::AllFunctions()->must()->invoke()->BFunction();
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $fun);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($fun, $violation->artifact());
        $this->assertSame($fun, $violation->violator());
    }

    public function test_invoke_5() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun2_dep));

        $def = function () { 
            Dicto::AFunction()->means()->functions()->with()->name("a_function");
            Dicto::CFunction()->means()->functions()->with()->name("c_function");
            Dicto::only()->AFunction()->can()->invoke()->CFunction();
        };
        $violations = $this->get_violations($def, $fun);

        $this->assertCount(0, $violations);
    }

    public function test_invoke_6() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun1_dep));

        $def = function () { 
            Dicto::AFunction()->means()->functions()->with()->name("a_function");
            Dicto::CFunction()->means()->functions()->with()->name("c_function");
            Dicto::only()->AFunction()->can()->invoke()->CFunction();
        };
        $violations = $this->get_violations($def, $fun);

        $this->assertCount(0, $violations);
    }

    public function test_invoke_7() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun2_dep));

        $def = function () { 
            Dicto::BFunction()->means()->functions()->with()->name("b_function");
            Dicto::CFunction()->means()->functions()->with()->name("c_function");
            Dicto::only()->CFunction()->can()->invoke()->BFunction();
        };
        $violations = $this->get_violations($def, $fun);

        $this->assertCount(0, $violations);
    }

    // CONTAINS TEXT

    public function test_contains_text_1() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $def = function () { 
            Dicto::AllClasses()->means()->classes();
            Dicto::AllClasses()->cannot()->contain_text("foo");
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $cls);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($cls, $violation->artifact());
        $violator = $violation->violator();
        $this->assertInstanceOf("Lechimp\\Dicto\\Verification\\SourceCodeLineArtifact", $violator);
        $this->assertEquals(0, $violator->start_line());
        $this->assertEquals(0, $violator->end_line());
        $this->assertEquals("foobar", $violator->source());
    }

    public function test_contains_text_2() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $def = function () { 
            Dicto::AllClasses()->means()->classes();
            Dicto::AllClasses()->cannot()->contain_text("baz");
        };
        $violations = $this->get_violations($def, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_contains_text_3() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $def = function () { 
            Dicto::AllClasses()->means()->classes();
            Dicto::AllClasses()->must()->contain_text("foo");
        };
        $violations = $this->get_violations($def, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_contains_text_4() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $def = function () { 
            Dicto::AllClasses()->means()->classes();
            Dicto::AllClasses()->must()->contain_text("baz");
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $cls);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($cls, $violation->artifact());
        $this->assertSame($cls, $violation->violator());
    }

    public function test_contains_text_5() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $def = function () { 
            Dicto::AClass()->means()->classes()->with()->name("AClass");
            Dicto::only()->AClass()->can()->contain_text("foo");
        };
        $violations = $this->get_violations($def, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_contains_text_6() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $def = function () { 
            Dicto::BClass()->means()->classes()->with()->name("BClass");
            Dicto::only()->BClass()->can()->contain_text("foo");
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $cls);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($cls, $violation->artifact());
        $violator = $violation->violator();
        $this->assertInstanceOf("Lechimp\\Dicto\\Verification\\SourceCodeLineArtifact", $violator);
        $this->assertEquals(0, $violator->start_line());
        $this->assertEquals(0, $violator->end_line());
        $this->assertEquals("foobar", $violator->source());
    }

    public function test_contains_text_7() {
        $cls = new ClassMock("AClass", array(), array(), "bar\n\nfoo");

        $def = function () { 
            Dicto::AllClasses()->means()->classes();
            Dicto::AllClasses()->cannot()->contain_text("foo");
        };
        list($violations, $rule) = $this->get_violations_and_rule($def, $cls);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($cls, $violation->artifact());
        $violator = $violation->violator();
        $this->assertInstanceOf("Lechimp\\Dicto\\Verification\\SourceCodeLineArtifact", $violator);
        $this->assertEquals(2, $violator->start_line());
        $this->assertEquals(2, $violator->end_line());
        $this->assertEquals("foo", $violator->source());
    }
}
