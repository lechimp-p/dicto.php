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

require_once(__DIR__."/RuleTest.php");

class InvokeTest extends RuleTest {
    /**
     * @return  R\Schema
     */
    public function schema() {
        return new R\Invoke();
    }

    // Parsing

    public function test_classes_cannot_invoke_functions() {
        $res = $this->parse("Classes cannot invoke: Functions");

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


    // Indexing

    public function test_index_method_call() {
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        \$foo->bar();
    }
}

CODE;

        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $code)
            ->willReturn("file");
        $this->expect_class($insert_mock, "AClass", "file", 3, 7)
            ->willReturn("class");
        $this->expect_method($insert_mock, "a_method", "class", "file", 4, 6)
            ->willReturn("method");
        $insert_mock
            ->expects($this->once())
            ->method("_method_reference")
            ->with
                ( "bar"
                , "file"
                , 5
                , 9
                )
            ->willReturn("method_reference");
        $insert_mock
            ->expects($this->exactly(2))
            ->method("_relation")
            ->withConsecutive
                ( array
                    ( "class"
                    , "invoke"
                    , "method_reference"
                    , "file"
                    , 5
                    )
                , array
                    ( "method"
                    , "invoke"
                    , "method_reference"
                    , "file"
                    , 5
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $code);
    }

    public function test_index_function_call() {
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        foobar();
    }
}

CODE;

        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $code)
            ->willReturn("file");
        $this->expect_class($insert_mock, "AClass", "file", 3, 7)
            ->willReturn("class");
        $this->expect_method($insert_mock, "a_method", "class", "file", 4, 6)
            ->willReturn("method");
        $insert_mock
            ->expects($this->once())
            ->method("_function_reference")
            ->with
                ( "foobar"
                , "file"
                , 5
                , 9
                )
            ->willReturn("function_reference");
        $insert_mock
            ->expects($this->exactly(2))
            ->method("_relation")
            ->withConsecutive
                ( array
                    ( "class"
                    , "invoke"
                    , "function_reference"
                    , "file"
                    , 5
                    )
                , array
                    ( "method"
                    , "invoke"
                    , "function_reference"
                    , "file"
                    , 5
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $code);
    }

    public function test_index_exit() {
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        exit;
    }
}

CODE;

        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $code)
            ->willReturn("file");
        $this->expect_class($insert_mock, "AClass", "file", 3, 7)
            ->willReturn("class");
        $this->expect_method($insert_mock, "a_method", "class", "file", 4, 6)
            ->willReturn("method");
        $insert_mock
            ->expects($this->once())
            ->method("_language_construct")
            ->with
                ( "exit"
                )
            ->willReturn("exit");
        $insert_mock
            ->expects($this->exactly(2))
            ->method("_relation")
            ->withConsecutive
                ( array
                    ( "class"
                    , "invoke"
                    , "exit"
                    , "file"
                    , 5
                    )
                , array
                    ( "method"
                    , "invoke"
                    , "exit"
                    , "file"
                    , 5
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $code);
    }

    public function test_index_die() {
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        die("foo");
    }
}

CODE;

        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $code)
            ->willReturn("file");
        $this->expect_class($insert_mock, "AClass", "file", 3, 7)
            ->willReturn("class");
        $this->expect_method($insert_mock, "a_method", "class", "file", 4, 6)
            ->willReturn("method");
        $insert_mock
            ->expects($this->once())
            ->method("_language_construct")
            ->with
                ( "die"
                )
            ->willReturn("die");
        $insert_mock
            ->expects($this->exactly(2))
            ->method("_relation")
            ->withConsecutive
                ( array
                    ( "class"
                    , "invoke"
                    , "die"
                    , "file"
                    , 5
                    )
                , array
                    ( "method"
                    , "invoke"
                    , "die"
                    , "file"
                    , 5
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $code);
    }

    // RULE 1

    protected function only_a_classes_can_invoke_functions() {
        $a_classes = new V\WithProperty
                ( new V\Classes()
                , new V\Name()
                , array(new Regexp("A.*"))
                );
        $methods_in_a_classes = new V\WithProperty
                ( new V\Methods()
                , new V\In()
                , array($a_classes)
                );
        return new R\Rule
            ( R\Rule::MODE_ONLY_CAN
            , new V\Any(array($a_classes, $methods_in_a_classes))
            , new R\Invoke()
            , array(new V\Functions())
            );
    }

    public function test_rule1_no_violation_1() {
        $rule = $this->only_a_classes_can_invoke_functions();
        $code = <<<CODE
<?php

class BClass {
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule1_no_violation_2() {
        $rule = $this->only_a_classes_can_invoke_functions();
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        some_function();
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule1_violation_1() {
        $rule = $this->only_a_classes_can_invoke_functions();
        $code = <<<CODE
<?php

class BClass {
    public function a_method() {
        some_function();
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array
            ( new Violation
                ( $rule
                , "source.php"
                , 5
                , "        some_function();"
                )

            , new Violation
                ( $rule
                , "source.php"
                , 5
                , "        some_function();"
                )
            );
        $this->assertEquals($expected, $violations);
    }

    // RULE 2

    protected function classes_must_invoke_a_functions() {
        return new R\Rule
            ( R\Rule::MODE_MUST
            , new V\Classes
            , new R\Invoke()
            , array(new V\WithProperty
                ( new V\Functions()
                , new V\Name()
                , array(new Regexp("a_.*"))
                ))
            );
    }

    public function test_rule2_no_violation_1() {
        $rule = $this->classes_must_invoke_a_functions();
        $code = <<<CODE
<?php

class SomeClass {
    public function a_method() {
        a_function();
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule2_violation_1() {
        $rule = $this->classes_must_invoke_a_functions();
        $code = <<<CODE
<?php

class SomeClass {
    public function a_method() {
        b_function();
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array
            ( new Violation
                ( $rule
                , "source.php"
                , 3
                , "class SomeClass {"
                )
            );
        $this->assertEquals($expected, $violations);
    }

    public function test_rule2_violation_2() {
        $rule = $this->classes_must_invoke_a_functions();
        $code = <<<CODE
<?php

class SomeClass {
    public function a_method() {
        \$foo->a_method();
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array
            ( new Violation
                ( $rule
                , "source.php"
                , 3
                , "class SomeClass {"
                )
            );
        $this->assertEquals($expected, $violations);
    }

    // RULE 3

    protected function classes_cannot_invoke_exit_or_die() {
        return new R\Rule
            ( R\Rule::MODE_CANNOT
            , new V\Classes
            , new R\Invoke()
            , array(new V\Any(
                [ new V\ExitOrDie()
                ]))
            );
    }

    public function test_rule3_no_violation_1() {
        $rule = $this->classes_cannot_invoke_exit_or_die();
        $code = <<<CODE
<?php

class SomeClass {
    public function a_method() {
        a_function();
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $this->assertCount(0, $violations);
    }

    public function test_rule3_violation_1() {
        $rule = $this->classes_cannot_invoke_exit_or_die();
        $code = <<<CODE
<?php

class SomeClass {
    public function a_method() {
        exit("foo");
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array
            ( new Violation
                ( $rule
                , "source.php"
                , 5
                , "        exit(\"foo\");"
                )
            );
        $this->assertEquals($expected, $violations);
    }

    public function test_rule3_violation_2() {
        $rule = $this->classes_cannot_invoke_exit_or_die();
        $code = <<<CODE
<?php

class SomeClass {
    public function a_method() {
        die;
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array
            ( new Violation
                ( $rule
                , "source.php"
                , 5
                , "        die;"
                )
            );
        $this->assertEquals($expected, $violations);
    }

    // RULE 4

    protected function classes_cannot_invoke_eval() {
        return new R\Rule
            ( R\Rule::MODE_CANNOT
            , new V\Classes
            , new R\Invoke()
            , array(new V\Eval_())
            );
    }

    public function test_rule4_violation_1() {
        $rule = $this->classes_cannot_invoke_eval();
        $code = <<<CODE
<?php

class SomeClass {
    public function a_method() {
        eval("echo foo;");
    }
}

CODE;

        $violations = $this->analyze($rule, $code);
        $expected = array
            ( new Violation
                ( $rule
                , "source.php"
                , 5
                , '        eval("echo foo;");'
                )
            );
        $this->assertEquals($expected, $violations);
    }
}
