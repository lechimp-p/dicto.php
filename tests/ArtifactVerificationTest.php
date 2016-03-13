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

    /**
     * @dataProvider has_subject_test_provider
     */
    public function test_has_subject(Def\Rule $rule, Ver\Artifact $artifact, $has_subject) {
        $res = $this->verifier->has_subject($rule, $artifact);
        $this->assertSame($has_subject, $res); 
    }

    public function has_subject_test_provider() {
        $every_class = Dicto::_every()->_class();
        $any_function = Dicto::_every()->_function();
        $named_class = Dicto::_every()->_class()->_with()->_name("Foo.*");
        return array
            (array
                ( $every_class->cannot()->invoke($any_function)
                , new ClassMock("AClass")
                , true
                )
            , array
                ( $every_class->cannot()->depend_on($named_class)
                , new ClassMock("AClass")
                , true
                )
            , array
                ( $every_class->must()->invoke($any_function)
                , new ClassMock("AClass")
                , true
                )
            , array
                ( $named_class->cannot()->invoke($any_function)
                , new ClassMock("AClass")
                , false
                )
            , array
                ( $every_class->cannot()->invoke($any_function)
                , new FunctionMock("a_function")
                , false
                )
            , array
                ( $every_class->cannot()->invoke($any_function)
                , new GlobalMock("a_global")
                , false
                )
            , array
                ( $every_class->cannot()->invoke($any_function)
                , new BuildinMock("a_buildin")
                , false
                )
            , array
                ( $every_class->cannot()->invoke($any_function)
                , new FileMock("a_file")
                , false
                )
            , array
                // With only, the subjects really are all classes that are
                // not contained in the variable.
                ( Dicto::only($named_class)->can()->invoke($any_function)
                , new ClassMock("AClass")
                , true
                )
            , array
                ( Dicto::only($named_class)->can()->invoke($any_function)
                , new ClassMock("FooClass")
                , false
                )
            );
    }

    // DEPEND ON

    public function test_depend_on_1() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("a_function");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep, $fun_dep));

        $classes = Dicto::_every()->_class();
        $rule = $classes->cannot()->depend_on($classes);

        $violations = $this->verifier->violations_in($rule, $cls);

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

        $classes = Dicto::_every()->_class()->_with()->_name("B.*");
        $rule = $classes->cannot()->depend_on($classes);

        $violations = $this->verifier->violations_in($rule, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_depend_on_3() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("a_function");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep, $fun_dep));

        $classes = Dicto::_every()->_class()->_with()->_name("A.*");
        $rule = $classes->cannot()->depend_on($classes);

        $violations = $this->verifier->violations_in($rule, $cls);

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

        $classes = Dicto::_every()->_class()->_with()->_name("A.*");
        $a_function = Dicto::_every()->_function()->_with()->_name("a_.*");
        $rule = $classes->cannot()->depend_on($a_function);

        $violations = $this->verifier->violations_in($rule, $cls);

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

        $classes = Dicto::_every()->_class();
        $a_function = Dicto::_every()->_function()->_with()->_name("a_.*");
        $rule = $classes->must()->depend_on($a_function);

        $violations = $this->verifier->violations_in($rule, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_depend_on_6() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep));

        $classes = Dicto::_every()->_class();
        $a_function = Dicto::_every()->_function()->_with()->_name("a_.*");
        $rule = $classes->must()->depend_on($a_function);

        $violations = $this->verifier->violations_in($rule, $cls);

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

        $classes = Dicto::_every()->_class()->_with()->_name("A.*");
        $a_function = Dicto::_every()->_function()->_with()->_name("a_.*");
        $rule = Dicto::only($classes)->can()->depend_on($a_function);

        $violations = $this->verifier->violations_in($rule, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_depend_on_8() {
        $cls_dep = new ClassMock("AnotherClass");
        $bldin_dep = new BuildinMock("@");
        $fun_dep = new FunctionMock("a_function");
        $cls = new ClassMock("AClass", array($cls_dep, $bldin_dep, $fun_dep));

        $classes = Dicto::_every()->_class()->_with()->_name("B.*");
        $a_function = Dicto::_every()->_function()->_with()->_name("a_.*");
        $rule = Dicto::only($classes)->can()->depend_on($a_function);

        $violations = $this->verifier->violations_in($rule, $cls);

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

        $functions = Dicto::_every()->_function();
        $silencer = Dicto::_every()->_buildin()->_with()->_name("@");
        $rule = $functions->cannot()->depend_on($silencer);

        $violations = $this->verifier->violations_in($rule, $fun);

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

        $functions = Dicto::_every()->_function();
        $c_function = Dicto::_every()->_function()->_with()->_name("c_function");
        $rule = $functions->cannot()->invoke($c_function);

        $violations = $this->verifier->violations_in($rule, $fun);

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

        $functions = Dicto::_every()->_function();
        $b_function = Dicto::_every()->_function()->_with()->_name("b_function");
        $rule = $functions->cannot()->invoke($b_function);

        $violations = $this->verifier->violations_in($rule, $fun);

        $this->assertCount(0, $violations);
    }

    public function test_invoke_3() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun2_dep));

        $functions = Dicto::_every()->_function();
        $c_function = Dicto::_every()->_function()->_with()->_name("c_function");
        $rule = $functions->must()->invoke($c_function);

        $violations = $this->verifier->violations_in($rule, $fun);

        $this->assertCount(0, $violations);
    }

    public function test_invoke_4() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun2_dep));

        $functions = Dicto::_every()->_function();
        $b_function = Dicto::_every()->_function()->_with()->_name("b_function");
        $rule = $functions->must()->invoke($b_function);

        $violations = $this->verifier->violations_in($rule, $fun);

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

        $a_function = Dicto::_every()->_function()->_with()->_name("a_function");
        $c_function = Dicto::_every()->_function()->_with()->_name("c_function");
        $rule = Dicto::only($a_function)->can()->invoke($c_function);

        $violations = $this->verifier->violations_in($rule, $fun);

        $this->assertCount(0, $violations);
    }

    public function test_invoke_6() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun1_dep));

        $a_function = Dicto::_every()->_function()->_with()->_name("a_function");
        $c_function = Dicto::_every()->_function()->_with()->_name("c_function");
        $rule = Dicto::only($a_function)->can()->invoke($c_function);

        $violations = $this->verifier->violations_in($rule, $fun);

        $this->assertCount(0, $violations);
    }

    public function test_invoke_7() {
        $cls_dep = new ClassMock("AClass");
        $bldin_dep = new BuildinMock("@");
        $fun1_dep = new FunctionMock("b_function");
        $fun2_dep = new FunctionMock("c_function");
        $fun = new FunctionMock("a_function", array($cls_dep, $bldin_dep, $fun1_dep), array($fun2_dep));

        $c_function = Dicto::_every()->_function()->_with()->_name("c_function");
        $b_function = Dicto::_every()->_function()->_with()->_name("b_function");
        $rule = Dicto::only($c_function)->can()->invoke($b_function);

        $violations = $this->verifier->violations_in($rule, $fun);

        $this->assertCount(0, $violations);
    }

    // CONTAINS TEXT

    public function test_contains_text_1() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $classes = Dicto::_every()->_class();
        $rule = $classes->cannot()->contain_text("foo");

        $violations = $this->verifier->violations_in($rule, $cls);

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

        $classes = Dicto::_every()->_class();
        $rule = $classes->cannot()->contain_text("baz");

        $violations = $this->verifier->violations_in($rule, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_contains_text_3() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $classes = Dicto::_every()->_class();
        $rule = $classes->must()->contain_text("foo");

        $violations = $this->verifier->violations_in($rule, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_contains_text_4() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $classes = Dicto::_every()->_class();
        $rule = $classes->must()->contain_text("baz");

        $violations = $this->verifier->violations_in($rule, $cls);

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame($rule, $violation->rule());
        $this->assertSame($cls, $violation->artifact());
        $this->assertSame($cls, $violation->violator());
    }

    public function test_contains_text_5() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $a_classes = Dicto::_every()->_class()->_with()->_name("AClass");
        $rule = Dicto::only($a_classes)->can()->contain_text("foo");

        $violations = $this->verifier->violations_in($rule, $cls);

        $this->assertCount(0, $violations);
    }

    public function test_contains_text_6() {
        $cls = new ClassMock("AClass", array(), array(), "foobar");

        $b_classes = Dicto::_every()->_class()->_with()->_name("BClass");
        $rule = Dicto::only($b_classes)->can()->contain_text("foo");

        $violations = $this->verifier->violations_in($rule, $cls);

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

        $classes = Dicto::_every()->_class();
        $rule = $classes->cannot()->contain_text("foo");

        $violations = $this->verifier->violations_in($rule, $cls);

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
