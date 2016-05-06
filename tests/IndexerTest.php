<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

use Lechimp\Dicto;
use Lechimp\Dicto\Analysis\Consts;
use Lechimp\Dicto\Indexer\Insert;

define("__IndexerTest_PATH_TO_SRC", __DIR__."/data/src");

class InsertMock implements Insert {
    public $entities = array();
    public $references = array();
    public $dependencies = array();
    public $invocations = array();

    public function entity($type, $name, $file, $start_line, $end_line, $source) {
        $id = count($this->entities) + count($this->references);
        $this->entities[] = array
            ( "id" => $id
            , "type" => $type
            , "name" => $name
            , "file" => $file
            , "start_line" => $start_line
            , "end_line" => $end_line
            , "source" => $source
            );
        return  $id;
    }

    public function reference($type, $name, $file, $line) {
        $id = count($this->entities) + count($this->references);
        $this->references[] = array
            ( "id" => $id
            , "type" => $type
            , "name" => $name
            , "file" => $file
            , "line" => $line
            );
        return $id;
    }


    public function dependency($dependent_id, $dependency_id, $file, $line, $source_line) {
        $this->dependencies[] = array
            ( "dependent_id" => $dependent_id
            , "dependency_id" => $dependency_id
            , "file" => $file
            , "line" => $line
            , "source_line" => $source_line
            );
    }

    public function invocation($invoker_id, $invokee_id, $file, $line, $source_line) {
        $this->invocations[] = array
            ( "invoker_id" => $invoker_id
            , "invokee_id" => $invokee_id
            , "file" => $file
            , "line" => $line
            , "source_line" => $source_line
            );
    }

    public function get_id($name) {
        foreach ($this->entities as $entity) {
            if ($entity["name"] == $name) {
                return $entity["id"];
            }
        }
        foreach ($this->references as $ref) {
            if ($ref["name"] == $name) {
                return $ref["id"];
            }
        }
        assert(false, "entity or reference named '$name' exists");
    }

    public function get_ids($name, $amount) {
        $ids = array();
        foreach ($this->references as $ref) {
            if ($ref["name"] == $name) {
                $ids[] = $ref["id"];
            }
        }
        assert('count($ids) ==  $amount');
        return $ids;
    }
} 

abstract class IndexerTest extends PHPUnit_Framework_TestCase {
    const PATH_TO_SRC = __IndexerTest_PATH_TO_SRC;

    abstract protected function get_indexer();

    public function setUp() {
        $this->indexer = $this->get_indexer();
        $this->insert_mock = new InsertMock();
        $this->indexer->use_insert($this->insert_mock);
        $this->indexer->set_project_root_to(IndexerTest::PATH_TO_SRC);
    }

    public function test_is_indexer() {
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Indexer\\Indexer", $this->indexer);
    }

    public function test_entity_A1_class() {
        $this->indexer->index_file("A1.php");
        $source = <<<PHP
class A1 {
    public function invoke_a_function() {
        return a_bogus_function();
    }    
}
PHP;
        $this->assertCount(3, $this->insert_mock->entities);
        $entity = null;
        foreach($this->insert_mock->entities as $e) {
            if ($e["type"] == Consts::CLASS_ENTITY) {
                $entity = $e;
            } 
        }
        $this->assertNotNull($entity);
        $this->assertEquals("A1", $entity["name"]);
        $this->assertEquals("A1.php", $entity["file"]);
        $this->assertEquals(11, $entity["start_line"]);
        $this->assertEquals(15, $entity["end_line"]);
        $this->assertEquals($source, $entity["source"]);
    }

    public function test_entity_A1_file() {
        $this->indexer->index_file("A1.php");
        $source = <<<PHP
<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

class A1 {
    public function invoke_a_function() {
        return a_bogus_function();
    }    
}

PHP;
        $this->assertCount(3, $this->insert_mock->entities);
        $entity = null;
        foreach($this->insert_mock->entities as $e) {
            if ($e["type"] == Consts::FILE_ENTITY) {
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
        $this->assertEquals($source, $entity["source"]);
    }

    public function test_entity_A1_method() {
        $this->indexer->index_file("A1.php");
        $source = <<<PHP
    public function invoke_a_function() {
        return a_bogus_function();
    }    
PHP;
        $this->assertCount(3, $this->insert_mock->entities);
        $entity = null;
        foreach($this->insert_mock->entities as $e) {
            if ($e["type"] == Consts::METHOD_ENTITY) {
                $entity = $e;
            }
        }
        $this->assertNotNull($entity);
        $this->assertEquals("invoke_a_function", $entity["name"]);
        $this->assertEquals("A1.php", $entity["file"]);
        $this->assertEquals(12, $entity["start_line"]);
        $this->assertEquals(14, $entity["end_line"]);
        $this->assertEquals($source, $entity["source"]);
    }

    public function test_references_A1_a_bogus_function() {
        $this->indexer->index_file("A1.php");
        $a_bogus_function_id = $this->insert_mock->get_id("a_bogus_function");
        $expected_refs = array
            ( array
                ( "id" => $a_bogus_function_id
                , "type" => Consts::FUNCTION_ENTITY
                , "name" => "a_bogus_function"
                , "file" => "A1.php"
                , "line" => 13
                )
            );
        $this->assertEquals($expected_refs, $this->insert_mock->references);
    }

    public function test_entity_A1_dependencies() {
        $this->indexer->index_file("A1.php");
        $A1_id = $this->insert_mock->get_id("A1");
        $invoke_a_function_id = $this->insert_mock->get_id("invoke_a_function");
        $a_bogus_function_id = $this->insert_mock->get_id("a_bogus_function");
        $expected_dep_A1 = array
            ( "dependent_id" => $A1_id
            , "dependency_id" => $a_bogus_function_id
            , "file" => "A1.php"
            , "line" => 13
            , "source_line" => "        return a_bogus_function();"
            );
        $expected_dep_invoke_a_function = array
            ( "dependent_id" => $invoke_a_function_id
            , "dependency_id" => $a_bogus_function_id
            , "file" => "A1.php"
            , "line" => 13
            , "source_line" => "        return a_bogus_function();"
            );

        $this->assertCount(2, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_A1, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_invoke_a_function, $this->insert_mock->dependencies);
    }

    public function test_entity_A1_invocations() {
        $this->indexer->index_file("A1.php");
        $A1_id = $this->insert_mock->get_id("A1");
        $invoke_a_function_id = $this->insert_mock->get_id("invoke_a_function");
        $a_bogus_function_id = $this->insert_mock->get_id("a_bogus_function");
        $expected_inv_invoke_a_function = array
            ( "invoker_id" => $invoke_a_function_id
            , "invokee_id" => $a_bogus_function_id
            , "file" => "A1.php"
            , "line" => 13
            , "source_line" => "        return a_bogus_function();"
            );
        $expected_inv_A1 = array
            ( "invoker_id" => $A1_id
            , "invokee_id" => $a_bogus_function_id
            , "file" => "A1.php"
            , "line" => 13
            , "source_line" => "        return a_bogus_function();"
            );

        $this->assertCount(2, $this->insert_mock->invocations);
        $this->assertContains($expected_inv_invoke_a_function, $this->insert_mock->invocations);
        $this->assertContains($expected_inv_A1, $this->insert_mock->invocations);
    }

    public function test_references_A2_invoke_a_function() {
        $this->indexer->index_file("A2.php");
        $invoke_a_function_id = $this->insert_mock->get_id("invoke_a_function");
        $expected_refs = array
            ( array
                ( "id" => $invoke_a_function_id
                , "type" => Consts::METHOD_ENTITY
                , "name" => "invoke_a_function"
                , "file" => "A2.php"
                , "line" => 13
                )
            );
        $this->assertEquals($expected_refs, $this->insert_mock->references);
    }

    public function test_entity_A2_dependencies() {
        $this->indexer->index_file("A2.php");
        $A2_id = $this->insert_mock->get_id("A2");
        $invoke_a_function_id = $this->insert_mock->get_id("invoke_a_function");
        $invoke_a_method_id = $this->insert_mock->get_id("invoke_a_method");
        $expected_dep_A2 = array
            ( "dependent_id" => $A2_id
            , "dependency_id" => $invoke_a_function_id
            , "file" => "A2.php"
            , "line" => 13
            , "source_line" => '        return $obj->invoke_a_function();'
            );
        $expected_dep_invoke_a_method= array
            ( "dependent_id" => $invoke_a_method_id
            , "dependency_id" => $invoke_a_function_id
            , "file" => "A2.php"
            , "line" => 13
            , "source_line" => '        return $obj->invoke_a_function();'
            );

        $this->assertCount(2, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_A2, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_invoke_a_method, $this->insert_mock->dependencies);
    }

    public function test_entity_A2_invocations() {
        $this->indexer->index_file("A2.php");
        $A2_id = $this->insert_mock->get_id("A2");
        $invoke_a_function_id = $this->insert_mock->get_id("invoke_a_function");
        $invoke_a_method_id = $this->insert_mock->get_id("invoke_a_method");
        $expected_inv_invoke_a_method = array
            ( "invoker_id" => $invoke_a_method_id
            , "invokee_id" => $invoke_a_function_id
            , "file" => "A2.php"
            , "line" => 13
            , "source_line" => '        return $obj->invoke_a_function();'
            );
        $expected_inv_A2 = array
            ( "invoker_id" => $A2_id
            , "invokee_id" => $invoke_a_function_id
            , "file" => "A2.php"
            , "line" => 13
            , "source_line" => '        return $obj->invoke_a_function();'
            );

        $this->assertCount(2, $this->insert_mock->invocations);
        $this->assertContains($expected_inv_invoke_a_method, $this->insert_mock->invocations);
        $this->assertContains($expected_inv_A2, $this->insert_mock->invocations);
    }

    public function test_references_A3_glob() {
        $this->indexer->index_file("A3.php");
        $glob_ids = $this->insert_mock->get_ids("glob", 2);
        $expected_ref_1 = array
            ( "id" => $glob_ids[0]
            , "type" => Consts::GLOBAL_ENTITY
            , "name" => "glob"
            , "file" => "A3.php"
            , "line" => 13
            );
        $expected_ref_2 = array
            ( "id" => $glob_ids[1]
            , "type" => Consts::GLOBAL_ENTITY
            , "name" => "glob"
            , "file" => "A3.php"
            , "line" => 17
            );

        $this->assertCount(2, $this->insert_mock->references);
        $this->assertContains($expected_ref_1, $this->insert_mock->references);
        $this->assertContains($expected_ref_2, $this->insert_mock->references);
    }

    public function test_entity_A3_dependencies() {
        $this->indexer->index_file("A3.php");
        $A3_id = $this->insert_mock->get_id("A3");
        $use_global_by_keyword_id = $this->insert_mock->get_id("use_global_by_keyword");
        $use_global_by_array_id = $this->insert_mock->get_id("use_global_by_array");
        $glob_ids = $this->insert_mock->get_ids("glob", 2); 
        $expected_dep_A3_1 = array
            ( "dependent_id" => $A3_id
            , "dependency_id" => $glob_ids[0]
            , "file" => "A3.php"
            , "line" => 13
            , "source_line" => '        global $glob;'
            );
        $expected_dep_A3_2 = array
            ( "dependent_id" => $A3_id
            , "dependency_id" => $glob_ids[1]
            , "file" => "A3.php"
            , "line" => 17
            , "source_line" => '        $glob = $GLOBALS["glob"];'
            );
        $expected_dep_use_global_by_keyword = array
            ( "dependent_id" => $use_global_by_keyword_id
            , "dependency_id" => $glob_ids[0]
            , "file" => "A3.php"
            , "line" => 13
            , "source_line" => '        global $glob;'
            );
        $expected_dep_use_global_by_array = array
            ( "dependent_id" => $use_global_by_array_id
            , "dependency_id" => $glob_ids[1]
            , "file" => "A3.php"
            , "line" => 17
            , "source_line" => '        $glob = $GLOBALS["glob"];'
            );

        $this->assertCount(4, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_A3_1, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_A3_2, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_use_global_by_keyword, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_use_global_by_array, $this->insert_mock->dependencies);
    }

    public function test_references_A4_use_stfu() {
        $this->indexer->index_file("A4.php");
        $stfu_op_id = $this->insert_mock->get_id("@");
        $stfu_fun_id = $this->insert_mock->get_id("stfu");
        $expected_ref_1 = array
            ( "id" => $stfu_op_id
            , "type" => Consts::LANGUAGE_CONSTRUCT_ENTITY
            , "name" => "@"
            , "file" => "A4.php"
            , "line" => 13
            );
        $expected_ref_2 = array
            ( "id" => $stfu_fun_id
            , "type" => Consts::FUNCTION_ENTITY
            , "name" => "stfu"
            , "file" => "A4.php"
            , "line" => 13
            );

        $this->assertCount(2, $this->insert_mock->references);
        $this->assertContains($expected_ref_1, $this->insert_mock->references);
        $this->assertContains($expected_ref_2, $this->insert_mock->references);
    }

    public function test_entity_A4_dependencies() {
        $this->indexer->index_file("A4.php");
        $A4_id = $this->insert_mock->get_id("A4");
        $use_stfu_id = $this->insert_mock->get_id("use_stfu");
        $stfu_op_id = $this->insert_mock->get_id("@");
        $stfu_fun_id = $this->insert_mock->get_id("stfu");
        $expected_dep_A4_1 = array
            ( "dependent_id" => $A4_id
            , "dependency_id" => $stfu_op_id
            , "file" => "A4.php"
            , "line" => 13
            , "source_line" => '        return @stfu();'
            );
        $expected_dep_A4_2 = array
            ( "dependent_id" => $A4_id
            , "dependency_id" => $stfu_fun_id
            , "file" => "A4.php"
            , "line" => 13
            , "source_line" => '        return @stfu();'
            );
        $expected_dep_use_stfu_1 = array
            ( "dependent_id" => $use_stfu_id
            , "dependency_id" => $stfu_op_id
            , "file" => "A4.php"
            , "line" => 13
            , "source_line" => '        return @stfu();'
            );
        $expected_dep_use_stfu_2 = array
            ( "dependent_id" => $use_stfu_id
            , "dependency_id" => $stfu_fun_id
            , "file" => "A4.php"
            , "line" => 13
            , "source_line" => '        return @stfu();'
            );

        $this->assertCount(4, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_A4_1, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_A4_2, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_use_stfu_1, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_use_stfu_2, $this->insert_mock->dependencies);
    }

    public function test_entity_A4_invocations() {
        $this->indexer->index_file("A4.php");
        $A4_id = $this->insert_mock->get_id("A4");
        $use_stfu_id = $this->insert_mock->get_id("use_stfu");
        $stfu_fun_id = $this->insert_mock->get_id("stfu");
        $expected_inv_use_stfu = array
            ( "invoker_id" => $use_stfu_id
            , "invokee_id" => $stfu_fun_id
            , "file" => "A4.php"
            , "line" => 13
            , "source_line" => '        return @stfu();'
            );
        $expected_inv_A4 = array
            ( "invoker_id" => $A4_id
            , "invokee_id" => $stfu_fun_id
            , "file" => "A4.php"
            , "line" => 13
            , "source_line" => '        return @stfu();'
            );

        $this->assertCount(2, $this->insert_mock->invocations);
        $this->assertContains($expected_inv_use_stfu, $this->insert_mock->invocations);
        $this->assertContains($expected_inv_A4, $this->insert_mock->invocations);
    }

    public function test_entity_MD_file() {
        $this->indexer->index_file("MD.md");
        $source = <<<PHP
Some random content.

PHP;
        $this->assertCount(1, $this->insert_mock->entities);
        $entity = $this->insert_mock->entities[0];
        $this->assertEquals("MD.md", $entity["name"]);
        $this->assertEquals("MD.md", $entity["file"]);
        $this->assertEquals(1, $entity["start_line"]);
        $this->assertEquals(2, $entity["end_line"]);
        $this->assertEquals($source, $entity["source"]);
    }

    public function test_omits_closure_invocations() {
        $this->indexer->index_file("CallsClosure.php");
        $id = $this->insert_mock->get_id("CallsClosure");

        $this->assertCount(0, $this->insert_mock->invocations);
        $this->assertCount(0, $this->insert_mock->dependencies);
    }

    public function test_indexes_array_twice() {
        $this->indexer->index_file("IndexesTwice.php");
        $IndexesTwice_id = $this->insert_mock->get_id("IndexesTwice");
        $indexes_GLOBAL_twice_id = $this->insert_mock->get_id("indexes_GLOBAL_twice");
        $glob_ids = $this->insert_mock->get_ids("glob", 1); 
        $expected_dep_IndexesTwice_1 = array
            ( "dependent_id" => $IndexesTwice_id
            , "dependency_id" => $glob_ids[0]
            , "file" => "IndexesTwice.php"
            , "line" => 17
            , "source_line" => '        return $GLOBALS["glob"]["bar"];'
            );
        $expected_dep_indexes_GLOBAL_twice_1 = array
            ( "dependent_id" => $indexes_GLOBAL_twice_id
            , "dependency_id" => $glob_ids[0]
            , "file" => "IndexesTwice.php"
            , "line" => 17
            , "source_line" => '        return $GLOBALS["glob"]["bar"];'
            );

        $this->assertCount(2, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_IndexesTwice_1, $this->insert_mock->dependencies);
        $this->assertContains($expected_dep_indexes_GLOBAL_twice_1, $this->insert_mock->dependencies);
    }
}
