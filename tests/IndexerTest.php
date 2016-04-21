<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
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
 * a copy of the along with the code.
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
}
