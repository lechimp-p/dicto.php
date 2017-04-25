<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Regexp;
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

    // Parsing

    public function test_classes_cannot_depend_on_functions() {
        $res = $this->parse("Classes cannot depend on: Functions");

        $expected = array
            ( new R\Rule
                ( R\Rule::MODE_CANNOT
                , new V\Classes()
                , new R\DependOn
                , array(new V\Functions())
                )
            );
        $this->assertEquals($expected, $res->rules());
    }

    // Indexing

    public function test_index_global1() {
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
            ->willReturn("file");
        $this->expect_class($insert_mock, "AClass", "file", 3, 7)
            ->willReturn("class");
        $this->expect_method($insert_mock, "a_method", "class", "file", 4, 6)
            ->willReturn("method");
        $insert_mock
            ->expects($this->once())
            ->method("_global")
            ->with
                ( "foo"
                )
            ->willReturn("global");
        $insert_mock
            ->expects($this->exactly(2))
            ->method("_relation")
            ->withConsecutive
                ( array
                    ( "class"
                    , "depend on"
                    , "global"
                    , "file"
                    , 5
                    )
                , array
                    ( "method"
                    , "depend on"
                    , "global"
                    , "file"
                    , 5
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $code);
    }

    public function test_index_global2() {
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        \$bar = \$GLOBALS["foo"];
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
            ->method("_global")
            ->with
                ( "foo"
                )
            ->willReturn("global");
        $insert_mock
            ->expects($this->exactly(2))
            ->method("_relation")
            ->withConsecutive
                ( array
                    ( "class"
                    , "depend on"
                    , "global"
                    , "file"
                    , 5
                    )
                , array
                    ( "method"
                    , "depend on"
                    , "global"
                    , "file"
                    , 5
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $code);
    }

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
                    , "depend on"
                    , "method_reference"
                    , "file"
                    , 5
                    )
                , array
                    ( "method"
                    , "depend on"
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
                    , "depend on"
                    , "function_reference"
                    , "file"
                    , 5
                    )
                , array
                    ( "method"
                    , "depend on"
                    , "function_reference"
                    , "file"
                    , 5
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $code);
    }

    public function test_index_error_suppressor() {
        $code = <<<CODE
<?php

class AClass {
    public function a_method() {
        @foobar();
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
                ( "@"
                )
            ->willReturn("error_suppressor");
        $insert_mock
            ->expects($this->once())
            ->method("_function_reference")
            ->with
                ( "foobar"
                , "file"
                , 5
                , 10
                )
            ->willReturn("function_reference");
        $insert_mock
            ->expects($this->exactly(4))
            ->method("_relation")
            ->withConsecutive
                ( array
                    ( "class"
                    , "depend on"
                    , "error_suppressor"
                    , "file"
                    , 5
                    )
                , array
                    ( "method"
                    , "depend on"
                    , "error_suppressor"
                    , "file"
                    , 5
                    )
                , array
                    ( "class"
                    , "depend on"
                    , "function_reference"
                    , "file"
                    , 5
                    )
                , array
                    ( "method"
                    , "depend on"
                    , "function_reference"
                    , "file"
                    , 5
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $code);
    }


    // RULE 1

    protected function only_a_classes_can_depend_on_globals() {
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
                , array(new Regexp("a_.*"))
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
        $rule = $this->classes_must_depend_on_a_globals();
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
                , 3
                , "class SomeClass {"
                )
            );
        $this->assertEquals($expected, $violations);
    }
}
