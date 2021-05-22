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
use Lechimp\Dicto\Rules as R;
use Lechimp\Dicto\Variables as V;
use Lechimp\Dicto\Analysis\Violation;

require_once(__DIR__ . "/RuleTest.php");

class ContainTextTest extends RuleTest
{
    /**
     * @return  R\Schema
     */
    public function schema()
    {
        return new R\ContainText();
    }

    // RULE 1

    protected function a_classes_must_contain_text_foo()
    {
        $a_classes = new V\WithProperty(
                    new V\Classes(),
                    new V\Name(),
                    array(new Regexp("A.*"))
                );
        return new R\Rule(
                R\Rule::MODE_MUST,
                $a_classes,
                new R\ContainText(),
                array(new Regexp("foo"))
            );
    }

    public function test_rule1_no_violation_1()
    {
        $rule = $this->a_classes_must_contain_text_foo();
        $code = <<<CODE
<?php

class BClass {
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule1_no_violation_2()
    {
        $rule = $this->a_classes_must_contain_text_foo();
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

    public function test_rule1_violation_1()
    {
        $rule = $this->a_classes_must_contain_text_foo();
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        global \$bar;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array( new Violation(
                    $rule,
                    "source.php",
                    3,
                    "class AClass {"
                )
            );
        $this->assertEquals($expected, $violations);
    }

    // RULE 2

    protected function a_classes_cannot_contain_text_foo()
    {
        $a_classes = new V\WithProperty(
                    new V\Classes(),
                    new V\Name(),
                    array(new Regexp("A.*"))
                );
        return new R\Rule(
                R\Rule::MODE_CANNOT,
                $a_classes,
                new R\ContainText(),
                array(new Regexp("foo"))
            );
    }

    public function test_rule2_no_violation_1()
    {
        $rule = $this->a_classes_cannot_contain_text_foo();
        $code = <<<CODE
<?php

class BClass {
    public function a_method() {
        global \$foo;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule2_no_violation_2()
    {
        $rule = $this->a_classes_cannot_contain_text_foo();
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule2_no_violation_3()
    {
        // Checks if start_line and end_line properties are used
        // correctly.
        $rule = $this->a_classes_cannot_contain_text_foo();
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
    }
}
function foo() { };

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }


    public function test_rule2_violation_1()
    {
        $rule = $this->a_classes_cannot_contain_text_foo();
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        global \$foo;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array( new Violation(
                    $rule,
                    "source.php",
                    5,
                    "        global \$foo;"
                )
            );
        $this->assertEquals($expected, $violations);
    }

    // RULE 3

    protected function files_cannot_contain_text_foo()
    {
        return new R\Rule(
                R\Rule::MODE_CANNOT,
                new V\Files(),
                new R\ContainText(),
                array(new Regexp("foo"))
            );
    }

    public function test_rule3_no_violation_1()
    {
        $rule = $this->files_cannot_contain_text_foo();
        $code = <<<CODE
<?php

class BClass {
    public function a_method() {
        global \$bar;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule3_no_violation_2()
    {
        $rule = $this->files_cannot_contain_text_foo();
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule3_violation_1()
    {
        $rule = $this->files_cannot_contain_text_foo();
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        global \$foo;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array( new Violation(
                    $rule,
                    "source.php",
                    5,
                    "        global \$foo;"
                )
            );
        $this->assertEquals($expected, $violations);
    }

    public function test_rule3_violation_2()
    {
        $rule = $this->files_cannot_contain_text_foo();
        $code = <<<CODE
foo
CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array( new Violation(
                    $rule,
                    "source.php",
                    1,
                    "foo"
                )
            );
        $this->assertEquals($expected, $violations);
    }

    // TODO: add tests on "only X can contain text"
    // TODO: add tests on "File Y MUST contain text"
    // TODO: add a test on the regexp
}
