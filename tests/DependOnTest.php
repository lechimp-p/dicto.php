<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Rules as R;
use Lechimp\Dicto\Variables as V;
use Lechimp\Dicto\Analysis\Violation;

require_once(__DIR__."/RuleTest.php");

class DependOnTest extends RuleTest {
    /**
     * @return  R\Schema
     */
    public function schema() {
        return new R\DependOn();
    }

    public function test_index_global() {
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        global \$foo;
    }
}

CODE;

        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $code)
            ->willReturn(1234);
        $this->expect_class($insert_mock, "AClass", 1234, 3, 7)
            ->willReturn(4321);
        $this->expect_method($insert_mock, "a_method", 4321, 1234, 4, 6)
            ->willReturn(23);
        $insert_mock
            ->expects($this->once())
            ->method("_global")
            ->with
                ( "foo"
                )
            ->willReturn(42);
        $insert_mock
            ->expects($this->exactly(2))
            ->method("_relation")
            ->withConsecutive
                ( array
                    ( 4321
                    , "depends on"
                    , 42
                    , 1234
                    , 5
                    )
                , array
                    ( 23
                    , "depends on"
                    , 42
                    , 1234
                    , 5
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $code);
    }
/*
    // RULE 1

    protected function only_a_classes_can_depend_on_globals() {
        $a_classes = new V\WithProperty
                ( new V\Classes()
                , new V\Name()
                , array("A.*")
                );
        $methods_in_a_classes = new V\WithProperty
                ( new V\Methods()
                , new V\In()
                , array($a_classes)
                );
        return new R\Rule
            ( R\Rule::MODE_ONLY_CAN
            , new V\Any(array($a_classes, $methods_in_a_classes))
            , new R\DependOn()
            , array(new V\Globals())
            );
    }

    public function test_rule1_no_violation_1() {
        $rule = $this->only_a_classes_can_depend_on_globals();
        $code = <<<CODE
<?php

class BClass {
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule1_no_violation_2() {
        $rule = $this->only_a_classes_can_depend_on_globals();
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        global \$foo;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule1_violation_1() {
        $rule = $this->only_a_classes_can_depend_on_globals();
        $code = <<<CODE
<?php

class BClass {
    public function a_method() {
        global \$foo;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array
            ( new Violation
                ( $rule
                , "source.php"
                , 5
                , "        global \$foo;"
                )

            , new Violation
                ( $rule
                , "source.php"
                , 5
                , "        global \$foo;"
                )
            );
        $this->assertEquals($expected, $violations);
    }

    // RULE 2

    protected function classes_must_depend_on_a_globals() {
        return new R\Rule
            ( R\Rule::MODE_MUST
            , new V\Classes
            , new R\DependOn()
            , array(new V\WithProperty
                ( new V\Globals()
                , new V\Name()
                , array("a_.*")
                ))
            );
    }

    public function test_rule2_no_violation_1() {
        $rule = $this->classes_must_depend_on_a_globals();
        $code = <<<CODE
<?php

class SomeClass {
    public function a_method() {
        global \$a_foo;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule2_no_violation_2() {
        $rule = $this->classes_must_depend_on_a_globals();
        $code = <<<CODE
<?php

class SomeClass {
    public function a_method() {
        global \$b_foo, \$a_foo;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule2_violation_1() {
        $rule = $this->only_a_classes_can_depend_on_globals();
        $code = <<<CODE
<?php

class SomeClass {
    public function a_method() {
        global \$b_foo;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array
            ( new Violation
                ( $rule
                , "source.php"
                , 5
                , "        global \$b_foo;"
                )

            );
        $this->assertEquals($expected, $violations);
    }
*/
}
