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
use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Indexer\Location;
use Lechimp\Dicto\Indexer\Indexer;
use Lechimp\Dicto\Indexer\CachesReferences;
use PhpParser\ParserFactory;
use PhpParser\Node as N;

require_once(__DIR__."/LoggerMock.php");

define("__IndexerTest_PATH_TO_SRC", __DIR__."/data/src");

class InsertMock implements Insert {
    use CachesReferences;

    public $files = array();
    public $entities = array();
    public $references = array();
    public $relations = array();

    public function __construct() {
        $this->relations = array
            ( "depend_on" => array()
            , "invoke" => array()
            );
    }

    public function source_file($name, $content) {
        $this->files[$name] = $content;
    }

    public function entity($type, $name, $file, $start_line, $end_line) {
        $id = count($this->entities) + count($this->references);
        $this->entities[] = array
            ( "id" => $id
            , "type" => $type
            , "name" => $name
            , "file" => $file
            , "start_line" => $start_line
            , "end_line" => $end_line
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


    public function relation($name, $entity_id, $reference_id) {
        $this->relations[$name][] = array
            ( "entity_id" => $entity_id
            , "reference_id" => $reference_id
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
        assert('count($ids) ==  $amount', count($ids)." ==  $amount");
        return $ids;
    }
} 


class IndexerNoIndexFile extends Indexer {
    public $indexed_files = array();

    public function index_file($base_dir, $path) {
        $this->indexed_files[] = $path;
    }
}


class IndexerTest extends PHPUnit_Framework_TestCase {
    const PATH_TO_SRC = __IndexerTest_PATH_TO_SRC;

    public function setUp() {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->logger_mock = new LoggerMock();
        $this->insert_mock = new InsertMock();
        $this->indexer = new Indexer
            ( $this->logger_mock
            , $this->parser
            , $this->insert_mock
            );
        (new \Lechimp\Dicto\Rules\ContainText())->register_listeners($this->indexer);
        (new \Lechimp\Dicto\Rules\DependOn())->register_listeners($this->indexer);
        (new \Lechimp\Dicto\Rules\Invoke())->register_listeners($this->indexer);
    }

    // TODO: add tests for logging

    public function test_is_indexer() {
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Indexer\\Indexer", $this->indexer);
    }

    public function test_A1_file() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "A1.php");
        $source = <<<PHP
<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

class A1 {
    public function invoke_a_function() {
        return a_bogus_function();
    }    
}

PHP;
        $this->assertEquals(array("A1.php" => $source), $this->insert_mock->files);
    }

    public function test_entity_A1_class() {
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

        $this->assertCount(2, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_A1, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_invoke_a_function, $this->insert_mock->relations["depend_on"]);
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

        $this->assertCount(2, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_A2, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_invoke_a_method, $this->insert_mock->relations["depend_on"]);
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

        $this->assertCount(4, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_A3_1, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_A3_2, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_use_global_by_keyword, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_use_global_by_array, $this->insert_mock->relations["depend_on"]);
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

        $this->assertCount(4, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_A4_1, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_A4_2, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_use_stfu_1, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_use_stfu_2, $this->insert_mock->relations["depend_on"]);
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
        $this->assertCount(0, $this->insert_mock->relations["depend_on"]);
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

        $this->assertCount(2, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_IndexesTwice_1, $this->insert_mock->relations["depend_on"]);
        $this->assertContains($expected_dep_indexes_GLOBAL_twice_1, $this->insert_mock->relations["depend_on"]);
    }

    public function test_ignores_call_to_variable_method() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "CallsVariableMethod.php");
        $id = $this->insert_mock->get_id("CallsVariableMethod");

        $this->assertCount(0, $this->insert_mock->relations["invoke"]);
        $this->assertCount(0, $this->insert_mock->relations["depend_on"]);
    }

    public function test_ignores_call_to_function_in_array() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "CallsFunctionInArray.php");
        $id = $this->insert_mock->get_id("CallsFunctionInArray");

        $this->assertCount(0, $this->insert_mock->relations["invoke"]);
        $this->assertCount(0, $this->insert_mock->relations["depend_on"]);
    }


    public function test_ignores_use_of_globals_with_var_index() {
        $this->indexer->index_file(IndexerTest::PATH_TO_SRC, "UsesGlobalsWithVarIndex.php");
        $id = $this->insert_mock->get_id("UsesGlobalsWithVarIndex");

        $this->assertCount(0, $this->insert_mock->relations["invoke"]);
        $this->assertCount(0, $this->insert_mock->relations["depend_on"]);
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

    // TODO: make this work again.
/*    public function test_faulty_php_logging() {
        $root = __DIR__."/data/src";
        $config = new Config(array(array
            ( "project" => array
                ( "root" => $this->root
                , "storage" => tempdir()
                )
            , "analysis" => array
                ( "ignore" => array
                    ( ".*\.omit_me"
                    )
                )
            )));
        $log = new LoggerMock();
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $indexer = new Indexer($parser, $root, new NullDB());
        $analyzer = new AnalyzerMock();
        $engine = new Engine($log, $config, $indexer, $analyzer);

        $engine->run();

        // Did it still index all files?
        $expected_files = array_filter(scandir($this->root), function($n) {
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
