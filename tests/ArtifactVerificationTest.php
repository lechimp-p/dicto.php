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
}
