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
    public $dependencies = array();
    public $invocations = array();

    public function entity($type, $name, $file, $start_line, $end_line, $source) {
        $this->entities[] = array
            ( "type" => $type
            , "name" => $name
            , "file" => $file
            , "start_line" => $start_line
            , "end_line" => $end_line
            , "source" => $source
            );
    }

    public function dependency($dependent_id, $dependency_id, $source_line) {
    }

    public function invokation($invoker_id, $invokee_id, $source_line) {
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
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC."/A1.php");
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
}
