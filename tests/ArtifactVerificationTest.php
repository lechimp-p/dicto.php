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
        $every_function = Dicto::_every()->_function();
        $named_class = Dicto::_every()->_class()->_with()->_name("Foo.*");
        return array
            (array
                ( $every_class->cannot()->invoke($every_function)
                , new ClassMock("AClass")
                , true
                )
            , array
                ( $every_class->cannot()->depend_on($named_class)
                , new ClassMock("AClass")
                , true
                )
            , array
                ( $every_class->must()->invoke($every_function)
                , new ClassMock("AClass")
                , true
                )
            , array
                ( $named_class->cannot()->invoke($every_function)
                , new ClassMock("AClass")
                , false
                )
            , array
                ( $every_class->cannot()->invoke($every_function)
                , new FunctionMock("a_function")
                , false
                )
            , array
                ( $every_class->cannot()->invoke($every_function)
                , new GlobalMock("a_global")
                , false
                )
            , array
                ( $every_class->cannot()->invoke($every_function)
                , new BuildinMock("a_buildin")
                , false
                )
            , array
                ( $every_class->cannot()->invoke($every_function)
                , new FileMock("a_file")
                , false
                )
            , array
                // With only, the subjects really are all classes that are
                // not contained in the variable.
                ( Dicto::only($named_class)->can()->invoke($every_function)
                , new ClassMock("AClass")
                , true
                )
            , array
                ( Dicto::only($named_class)->can()->invoke($every_function)
                , new ClassMock("FooClass")
                , false
                )
            );
    }
}
