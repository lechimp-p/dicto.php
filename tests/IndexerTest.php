<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto;

require_once(__DIR__."/IndexerExpectations.php");

class IndexerTest extends PHPUnit_Framework_TestCase {
    use IndexerExpectations;

    public function test_file_empty() {
        $source = <<<PHP
<?php
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn(23);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_class_definition() {
        $source = <<<PHP
<?php

class AClass {
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn(23);
        $this->expect_class($insert_mock, "AClass", 23, 3, 4)
            ->willReturn(42);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_method_definition() {
        $source = <<<PHP
<?php

class AClass {
    public function a_method() {
    }
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn(23);
        $this->expect_class($insert_mock, "AClass", 23, 3, 6)
            ->willReturn(42);
        $this->expect_method($insert_mock, "a_method", 42, 23, 4, 5)
            ->willReturn(1234);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }


    public function test_function_definition() {
        $source = <<<PHP
<?php

function a_function() {
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn(23);
        $this->expect_function($insert_mock, "a_function", 23, 3, 4)
            ->willReturn(42);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }


/*    public function test_method_uses_global() {
        $source = <<<PHP
<?php

class AClass {
    public function a_method() {
        global \$foo;
    }
}
PHP;
        $insert_mock = $this->getInsertMock();

        $insert_mock
            ->expects($this->exactly(2))
            ->method("definition")
            ->willReturnOnConsecutiveCalls(array(1,1),array(2,2))
            ->withConsecutive
                ( array
                    ( $this->equalTo("AClass")
                    , $this->equalTo(Variable::CLASS_TYPE)
                    )
                , array
                    ( $this->equalTo("a_method")
                    , $this->equalTo(Variable::METHOD_TYPE)
                    )
                );


        $insert_mock
            ->expects($this->once())
            ->method("name")
            ->willReturn(3)
            ->with
                ( $this->equalTo("foo")
                , $this->equalTo(Variable::GLOBAL_TYPE)
                );

        $insert_mock
            ->expects($this->exactly(2))
            ->method("relation")
            ->withConsecutive
                ( array
                    ( $this->equalTo(1) // AClass
                    , $this->equalTo(3) // foo
                    , $this->equalTo("depend on")
                    , $this->equalTo("source.php")
                    , $this->equalTo(5)
                    )
                , array
                    ( $this->equalTo(2) // a_method
                    , $this->equalTo(3) // foo
                    , $this->equalTo("depend on")
                    , $this->equalTo("source.php")
                    , $this->equalTo(5)
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }
*/

/*    public function test_function_uses_global() {
        $source = <<<PHP
<?php

function a_function() {
    global \$foo;
}
PHP;
        $insert_mock = $this->getInsertMock();

        $insert_mock
            ->expects($this->once())
            ->method("definition")
            ->willReturn(array(1,1))
            ->with
                ( $this->equalTo("a_function")
                , $this->equalTo(Variable::FUNCTION_TYPE)
                );

        $insert_mock
            ->expects($this->once())
            ->method("name")
            ->willReturn(2)
            ->with
                ( $this->equalTo("foo")
                , $this->equalTo(Variable::GLOBAL_TYPE)
                );

        $insert_mock
            ->expects($this->once())
            ->method("relation")
            ->with
                ( $this->equalTo(1) // a_function
                , $this->equalTo(2) // foo
                , $this->equalTo("depend on")
                , $this->equalTo("source.php")
                , $this->equalTo(4)
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }
*/

/*    public function test_function_invokes_function() {
        $source = <<<PHP
<?php

function a_function() {
    another_function();
}
PHP;
        $insert_mock = $this->getInsertMock();

        $insert_mock
            ->expects($this->once())
            ->method("definition")
            ->willReturn(array(1,1))
            ->with
                ( $this->equalTo("a_function")
                , $this->equalTo(Variable::FUNCTION_TYPE)
                );

        $insert_mock
            ->expects($this->exactly(2))
            ->method("name")
            ->willReturn(2)
            ->with
                ( $this->equalTo("another_function")
                , $this->equalTo(Variable::FUNCTION_TYPE)
                );

        $insert_mock
            ->expects($this->exactly(2))
            ->method("relation")
            ->withConsecutive
                ( array
                    ( $this->equalTo(1) // a_function
                    , $this->equalTo(2) // another_function
                    , $this->equalTo("depend on")
                    , $this->equalTo("source.php")
                    , $this->equalTo(4)
                    )
                , array
                    ( $this->equalTo(1) // a_function
                    , $this->equalTo(2) // another_function
                    , $this->equalTo("invoke")
                    , $this->equalTo("source.php")
                    , $this->equalTo(4)
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }
*/

/*    public function test_function_invokes_method() {
        $source = <<<PHP
<?php

function a_function() {
    \$foo->some_method();
}
PHP;
        $insert_mock = $this->getInsertMock();


        $insert_mock
            ->expects($this->once())
            ->method("definition")
            ->willReturn(array(1,1))
            ->with
                ( $this->equalTo("a_function")
                , $this->equalTo(Variable::FUNCTION_TYPE)
                );

        $insert_mock
            ->expects($this->exactly(2))
            ->method("name")
            ->willReturn(2)
            ->with
                ( $this->equalTo("some_method")
                , $this->equalTo(Variable::METHOD_TYPE)
                );

        $insert_mock
            ->expects($this->exactly(2))
            ->method("relation")
            ->withConsecutive
                ( array
                    ( $this->equalTo(1) // a_function
                    , $this->equalTo(2) // some_method
                    , $this->equalTo("depend on")
                    , $this->equalTo("source.php")
                    , $this->equalTo(4)
                    )
                , array
                    ( $this->equalTo(1) // a_function
                    , $this->equalTo(2) // some_method
                    , $this->equalTo("invoke")
                    , $this->equalTo("source.php")
                    , $this->equalTo(4)
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }
*/

/*    public function test_function_use_error_suppressor() {
        $source = <<<PHP
<?php

function a_function() {
    @\$foo->some_method();
}
PHP;
        $insert_mock = $this->getInsertMock();

        $insert_mock
            ->expects($this->once())
            ->method("definition")
            ->willReturn(array(1,1))
            ->with
                ( $this->equalTo("a_function")
                , $this->equalTo(Variable::FUNCTION_TYPE)
                );

        $insert_mock
            ->expects($this->exactly(3))
            ->method("name")
            ->willReturnOnConsecutiveCalls(2,3,3)
            ->withConsecutive
                ( array
                    ( $this->equalTo("@")
                    , $this->equalTo(Variable::LANGUAGE_CONSTRUCT_TYPE)
                    )
                , array
                    ( $this->equalTo("some_method")
                    , $this->equalTo(Variable::METHOD_TYPE)
                    )
                , array
                    ( $this->equalTo("some_method")
                    , $this->equalTo(Variable::METHOD_TYPE)
                    )
                );

        $insert_mock
            ->expects($this->exactly(3))
            ->method("relation")
            ->withConsecutive
                ( array
                    ( $this->equalTo(1) // a_function
                    , $this->equalTo(2) // @
                    , $this->equalTo("depend on")
                    , $this->equalTo("source.php")
                    , $this->equalTo(4)
                    )
                , array
                    ( $this->equalTo(1) // a_function
                    , $this->equalTo(3) // some_method
                    , $this->equalTo("depend on")
                    , $this->equalTo("source.php")
                    , $this->equalTo(4)
                    )
                , array
                    ( $this->equalTo(1) // a_function
                    , $this->equalTo(3) // some_method
                    , $this->equalTo("invoke")
                    , $this->equalTo("source.php")
                    , $this->equalTo(4)
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }
*/
/*    public function test_function_use_global() {
        $source = <<<PHP
<?php

function a_function() {
    return \$GLOBALS["foo"];
}
PHP;
        $insert_mock = $this->getInsertMock();

        $insert_mock
            ->expects($this->once())
            ->method("definition")
            ->willReturn(array(1,1))
            ->with
                ( $this->equalTo("a_function")
                , $this->equalTo(Variable::FUNCTION_TYPE)
                );

        $insert_mock
            ->expects($this->exactly(1))
            ->method("name")
            ->willReturnOnConsecutiveCalls(2)
            ->withConsecutive
                ( array
                    ( $this->equalTo("foo")
                    , $this->equalTo(Variable::GLOBAL_TYPE)
                    )
                );

        $insert_mock
            ->expects($this->exactly(1))
            ->method("relation")
            ->withConsecutive
                ( array
                    ( $this->equalTo(1) // a_function
                    , $this->equalTo(2) // global foo
                    , $this->equalTo("depend on")
                    , $this->equalTo("source.php")
                    , $this->equalTo(4)
                    )
                );

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }
*/
/*    public function test_entity_A1_class() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A1.php");

        $this->assertCount(3, $this->insert_mock->entities);
        $entity = null;
        foreach($this->insert_mock->entities as $e) {
            if ($e["type"] == Variable::CLASS_TYPE) {
                $entity = $e;
            } 
        }
        $this->assertNotNull($entity);
        $this->assertEquals("A1", $entity["name"]);
        $this->assertEquals("A1.php", $entity["file"]);
        $this->assertEquals(11, $entity["start_line"]);
        $this->assertEquals(15, $entity["end_line"]);
    }

    public function test_entity_A1_file() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A1.php");

        $this->assertCount(3, $this->insert_mock->entities);
        $entity = null;
        foreach($this->insert_mock->entities as $e) {
            if ($e["type"] == Variable::FILE_TYPE) {
                $entity = $e;
            }
        }
        $this->assertNotNull($entity);
        $this->assertEquals("A1.php", $entity["name"]);
        $this->assertEquals("A1.php", $entity["file"]);
        $this->assertEquals(1, $entity["start_line"]);
        # The file will actually contain one more line then it seems,
        # as the last line ends with a newline.
        $this->assertEquals(16, $entity["end_line"]);
    }

    public function test_entity_A1_method() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A1.php");

        $this->assertCount(3, $this->insert_mock->entities);
        $entity = null;
        foreach($this->insert_mock->entities as $e) {
            if ($e["type"] == Variable::METHOD_TYPE) {
                $entity = $e;
            }
        }
        $this->assertNotNull($entity);
        $this->assertEquals("invoke_a_function", $entity["name"]);
        $this->assertEquals("A1.php", $entity["file"]);
        $this->assertEquals(12, $entity["start_line"]);
        $this->assertEquals(14, $entity["end_line"]);
    }

    public function test_references_A1_a_bogus_function() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A1.php");
        $a_bogus_function_id = $this->insert_mock->get_id("a_bogus_function");
        $expected_refs = array
            ( array
                ( "id" => $a_bogus_function_id
                , "type" => Variable::FUNCTION_TYPE
                , "name" => "a_bogus_function"
                , "file" => "A1.php"
                , "line" => 13
                )
            );
        $this->assertEquals($expected_refs, $this->insert_mock->references);
    }

    public function test_entity_A1_dependencies() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A1.php");
        $A1_id = $this->insert_mock->get_id("A1");
        $invoke_a_function_id = $this->insert_mock->get_id("invoke_a_function");
        $a_bogus_function_id = $this->insert_mock->get_id("a_bogus_function");
        $expected_dep_A1 = array
            ( "entity_id" => $A1_id
            , "reference_id" => $a_bogus_function_id
            );
        $expected_dep_invoke_a_function = array
            ( "entity_id" => $invoke_a_function_id
            , "reference_id" => $a_bogus_function_id
            );

        $this->assertCount(2, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_A1, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_invoke_a_function, $this->insert_mock->relations["depend on"]);
    }

    public function test_entity_A1_invocations() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A1.php");
        $A1_id = $this->insert_mock->get_id("A1");
        $invoke_a_function_id = $this->insert_mock->get_id("invoke_a_function");
        $a_bogus_function_id = $this->insert_mock->get_id("a_bogus_function");
        $expected_inv_invoke_a_function = array
            ( "entity_id" => $invoke_a_function_id
            , "reference_id" => $a_bogus_function_id
            );
        $expected_inv_A1 = array
            ( "entity_id" => $A1_id
            , "reference_id" => $a_bogus_function_id
            );

        $this->assertCount(2, $this->insert_mock->relations["invoke"]);
        $this->assertContains($expected_inv_invoke_a_function, $this->insert_mock->relations["invoke"]);
        $this->assertContains($expected_inv_A1, $this->insert_mock->relations["invoke"]);
    }

    public function test_references_A2_invoke_a_function() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A2.php");
        $invoke_a_function_id = $this->insert_mock->get_id("invoke_a_function");
        $expected_refs = array
            ( array
                ( "id" => $invoke_a_function_id
                , "type" => Variable::METHOD_TYPE
                , "name" => "invoke_a_function"
                , "file" => "A2.php"
                , "line" => 13
                )
            );
        $this->assertEquals($expected_refs, $this->insert_mock->references);
    }

    public function test_entity_A2_dependencies() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A2.php");
        $A2_id = $this->insert_mock->get_id("A2");
        $invoke_a_function_id = $this->insert_mock->get_id("invoke_a_function");
        $invoke_a_method_id = $this->insert_mock->get_id("invoke_a_method");
        $expected_dep_A2 = array
            ( "entity_id" => $A2_id
            , "reference_id" => $invoke_a_function_id
            );
        $expected_dep_invoke_a_method= array
            ( "entity_id" => $invoke_a_method_id
            , "reference_id" => $invoke_a_function_id
            );

        $this->assertCount(2, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_A2, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_invoke_a_method, $this->insert_mock->relations["depend on"]);
    }

    public function test_entity_A2_invocations() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A2.php");
        $A2_id = $this->insert_mock->get_id("A2");
        $invoke_a_function_id = $this->insert_mock->get_id("invoke_a_function");
        $invoke_a_method_id = $this->insert_mock->get_id("invoke_a_method");
        $expected_inv_invoke_a_method = array
            ( "entity_id" => $invoke_a_method_id
            , "reference_id" => $invoke_a_function_id
            );
        $expected_inv_A2 = array
            ( "entity_id" => $A2_id
            , "reference_id" => $invoke_a_function_id
            );

        $this->assertCount(2, $this->insert_mock->relations["invoke"]);
        $this->assertContains($expected_inv_invoke_a_method, $this->insert_mock->relations["invoke"]);
        $this->assertContains($expected_inv_A2, $this->insert_mock->relations["invoke"]);
    }

    public function test_references_A3_glob() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A3.php");
        $glob_ids = $this->insert_mock->get_ids("glob", 2);
        $expected_ref_1 = array
            ( "id" => $glob_ids[0]
            , "type" => Variable::GLOBAL_TYPE
            , "name" => "glob"
            , "file" => "A3.php"
            , "line" => 13
            );
        $expected_ref_2 = array
            ( "id" => $glob_ids[1]
            , "type" => Variable::GLOBAL_TYPE
            , "name" => "glob"
            , "file" => "A3.php"
            , "line" => 17
            );

        $this->assertCount(2, $this->insert_mock->references);
        $this->assertContains($expected_ref_1, $this->insert_mock->references);
        $this->assertContains($expected_ref_2, $this->insert_mock->references);
    }

    public function test_entity_A3_dependencies() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A3.php");
        $A3_id = $this->insert_mock->get_id("A3");
        $use_global_by_keyword_id = $this->insert_mock->get_id("use_global_by_keyword");
        $use_global_by_array_id = $this->insert_mock->get_id("use_global_by_array");
        $glob_ids = $this->insert_mock->get_ids("glob", 2); 
        $expected_dep_A3_1 = array
            ( "entity_id" => $A3_id
            , "reference_id" => $glob_ids[0]
            );
        $expected_dep_A3_2 = array
            ( "entity_id" => $A3_id
            , "reference_id" => $glob_ids[1]
            );
        $expected_dep_use_global_by_keyword = array
            ( "entity_id" => $use_global_by_keyword_id
            , "reference_id" => $glob_ids[0]
            );
        $expected_dep_use_global_by_array = array
            ( "entity_id" => $use_global_by_array_id
            , "reference_id" => $glob_ids[1]
            );

        $this->assertCount(4, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_A3_1, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_A3_2, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_use_global_by_keyword, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_use_global_by_array, $this->insert_mock->relations["depend on"]);
    }

    public function test_references_A4_use_stfu() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A4.php");
        $stfu_op_id = $this->insert_mock->get_id("@");
        $stfu_fun_id = $this->insert_mock->get_id("stfu");
        $expected_ref_1 = array
            ( "id" => $stfu_op_id
            , "type" => Variable::LANGUAGE_CONSTRUCT_TYPE
            , "name" => "@"
            , "file" => "A4.php"
            , "line" => 13
            );
        $expected_ref_2 = array
            ( "id" => $stfu_fun_id
            , "type" => Variable::FUNCTION_TYPE
            , "name" => "stfu"
            , "file" => "A4.php"
            , "line" => 13
            );

        $this->assertCount(2, $this->insert_mock->references);
        $this->assertContains($expected_ref_1, $this->insert_mock->references);
        $this->assertContains($expected_ref_2, $this->insert_mock->references);
    }

    public function test_entity_A4_dependencies() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A4.php");
        $A4_id = $this->insert_mock->get_id("A4");
        $use_stfu_id = $this->insert_mock->get_id("use_stfu");
        $stfu_op_id = $this->insert_mock->get_id("@");
        $stfu_fun_id = $this->insert_mock->get_id("stfu");
        $expected_dep_A4_1 = array
            ( "entity_id" => $A4_id
            , "reference_id" => $stfu_op_id
            );
        $expected_dep_A4_2 = array
            ( "entity_id" => $A4_id
            , "reference_id" => $stfu_fun_id
            );
        $expected_dep_use_stfu_1 = array
            ( "entity_id" => $use_stfu_id
            , "reference_id" => $stfu_op_id
            );
        $expected_dep_use_stfu_2 = array
            ( "entity_id" => $use_stfu_id
            , "reference_id" => $stfu_fun_id
            );

        $this->assertCount(4, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_A4_1, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_A4_2, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_use_stfu_1, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_use_stfu_2, $this->insert_mock->relations["depend on"]);
    }

    public function test_entity_A4_invocations() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A4.php");
        $A4_id = $this->insert_mock->get_id("A4");
        $use_stfu_id = $this->insert_mock->get_id("use_stfu");
        $stfu_fun_id = $this->insert_mock->get_id("stfu");
        $expected_inv_use_stfu = array
            ( "entity_id" => $use_stfu_id
            , "reference_id" => $stfu_fun_id
            );
        $expected_inv_A4 = array
            ( "entity_id" => $A4_id
            , "reference_id" => $stfu_fun_id
            );

        $this->assertCount(2, $this->insert_mock->relations["invoke"]);
        $this->assertContains($expected_inv_use_stfu, $this->insert_mock->relations["invoke"]);
        $this->assertContains($expected_inv_A4, $this->insert_mock->relations["invoke"]);
    }

    public function test_entity_MD_file() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "MD.md");
        $source = <<<PHP
Some random content.

PHP;
        $this->assertCount(1, $this->insert_mock->entities);
        $entity = $this->insert_mock->entities[0];
        $this->assertEquals("MD.md", $entity["name"]);
        $this->assertEquals("MD.md", $entity["file"]);
        $this->assertEquals(1, $entity["start_line"]);
        $this->assertEquals(2, $entity["end_line"]);
    }

    public function test_ignores_closure_invocations() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "CallsClosure.php");
        $id = $this->insert_mock->get_id("CallsClosure");

        $this->assertCount(0, $this->insert_mock->relations["invoke"]);
        $this->assertCount(0, $this->insert_mock->relations["depend on"]);
    }

    public function test_indexes_array_twice() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "IndexesTwice.php");
        $IndexesTwice_id = $this->insert_mock->get_id("IndexesTwice");
        $indexes_GLOBAL_twice_id = $this->insert_mock->get_id("indexes_GLOBAL_twice");
        $glob_ids = $this->insert_mock->get_ids("glob", 1); 
        $expected_dep_IndexesTwice_1 = array
            ( "entity_id" => $IndexesTwice_id
            , "reference_id" => $glob_ids[0]
            );
        $expected_dep_indexes_GLOBAL_twice_1 = array
            ( "entity_id" => $indexes_GLOBAL_twice_id
            , "reference_id" => $glob_ids[0]
            );

        $this->assertCount(2, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_IndexesTwice_1, $this->insert_mock->relations["depend on"]);
        $this->assertContains($expected_dep_indexes_GLOBAL_twice_1, $this->insert_mock->relations["depend on"]);
    }

    public function test_ignores_call_to_variable_method() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "CallsVariableMethod.php");
        $id = $this->insert_mock->get_id("CallsVariableMethod");

        $this->assertCount(0, $this->insert_mock->relations["invoke"]);
        $this->assertCount(0, $this->insert_mock->relations["depend on"]);
    }

    public function test_ignores_call_to_function_in_array() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "CallsFunctionInArray.php");
        $id = $this->insert_mock->get_id("CallsFunctionInArray");

        $this->assertCount(0, $this->insert_mock->relations["invoke"]);
        $this->assertCount(0, $this->insert_mock->relations["depend on"]);
    }


    public function test_ignores_use_of_globals_with_var_index() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "UsesGlobalsWithVarIndex.php");
        $id = $this->insert_mock->get_id("UsesGlobalsWithVarIndex");

        $this->assertCount(0, $this->insert_mock->relations["invoke"]);
        $this->assertCount(0, $this->insert_mock->relations["depend on"]);
    }

    public function test_listener_registry() {
        $enter_e = array();
        $leave_e = array();
        $enter_m = array();
        $leave_m = array();

        $this->indexer
            ->on_enter_entity(null, function(Insert $i, Location $l, $type, $id, $node) use (&$enter_e) {
                $enter_e[] = $type;
            })
            ->on_leave_entity(null, function(Insert $i, Location $l, $type, $id, $node) use (&$leave_e) {
                $leave_e[] = $type;
            })
            ->on_enter_misc(array(N\Expr\FuncCall::class), function(Insert $i, Location $l, N\Expr\FuncCall $node) use (&$enter_m) {
                $enter_m[] = $node->name->parts[0];
            })
            ->on_leave_misc(array(N\Expr\FuncCall::class), function(Insert $i, Location $l, N\Expr\FuncCall $node) use (&$leave_m) {
                $leave_m[] = $node->name->parts[0];
            });

        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A1.php");

        $this->assertEquals(array(Variable::FILE_TYPE, Variable::CLASS_TYPE, Variable::METHOD_TYPE), $enter_e);
        $this->assertEquals($enter_e, array_reverse($leave_e));
        $this->assertEquals(array("a_bogus_function"), $enter_m);
        $this->assertEquals(array("a_bogus_function"), $leave_m);
    }

    public function test_index_directory() {
        $this->indexer = new IndexerNoIndexFile
            ( $this->logger_mock
            , $this->parser
            , $this->insert_mock
            );

        $this->indexer->index_directory(IndexerTest::PATH_TO_SRC, array(".*\\.omit_me"));
        $expected = array_filter(scandir(IndexerTest::PATH_TO_SRC), function($n) {
            return $n != "." && $n != ".." && $n != "A1.omit_me";
        });

        $this->assertEquals(count($expected), count($this->indexer->indexed_files));
        foreach ($expected as $e) {
            $this->assertContains($e, $this->indexer->indexed_files);
        }
    }

    public function test_faulty_php_logging() {
        $log = new LoggerMock();
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $indexer = new Indexer($log, $parser, new NullDB());

        $indexer->index_directory(IndexerTest::PATH_TO_SRC, array(".*\\.omit_me"));

        // Did it still index all files?
        $expected_files = array_filter(scandir(IndexerTest::PATH_TO_SRC), function($n) {
            return $n != "." && $n != ".." && $n != "A1.omit_me";
        });

        foreach ($expected_files as $e) {
            $expected = array(LogLevel::INFO, "indexing: $e", array());
            $this->assertContains($expected, $log->log);
        }

        $expected_error = array(LogLevel::ERROR, "in faulty.php: Syntax error, unexpected T_FUNCTION, expecting '{' on line 4", array());
        $this->assertContains($expected_error, $log->log);
    }*/
}
