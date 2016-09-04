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

    protected function only_a_classes_can_depend_on_globals() {
        return new R\Rule
            ( R\Rule::MODE_ONLY_CAN
            , new V\WithProperty
                ( new V\Classes()
                , new V\Name()
                , array("A.*")
                )
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
}
