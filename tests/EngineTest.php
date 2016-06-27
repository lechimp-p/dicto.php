<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;
use Lechimp\Dicto\App\Engine;
use Lechimp\Dicto\App\DBFactory;
use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Analysis\Analyzer;
use Lechimp\Dicto\Analysis\AnalyzerFactory;
use Lechimp\Dicto\Indexer\Indexer;
use Lechimp\Dicto\Indexer\IndexerFactory;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\App\Config;
use Lechimp\Dicto\Rules\RuleSet;
use PhpParser\ParserFactory;
use Doctrine\DBAL\DriverManager;
use Psr\Log\LogLevel;

require_once(__DIR__."/LoggerMock.php");
require_once(__DIR__."/tempdir.php");

class AnalyzerFactoryMock extends AnalyzerFactory {
    public $analyzer_mocks = array();
    public function __construct() {}
    public function build(Query $query) {
        $analyzer_mock = new AnalyzerMock();
        $this->analyzer_mocks[] = $analyzer_mock;
        return $analyzer_mock;
    }
}

class AnalyzerMock extends Analyzer {
    public $run_called = false;
    public function __construct() {}
    public function run() {
        $this->run_called = true;
    }
}

class IndexerFactoryMock extends IndexerFactory {
    public $indexer_mocks = array();
    public function __construct() {}
    public function build(Insert $insert) {
        $indexer_mock = new IndexerMock();
        $this->indexer_mocks[] = $indexer_mock;
        return $indexer_mock;
    }
}

class IndexerMock extends Indexer {
    public $indexed_files = array();
    public function __construct() {
        $this->log = new LoggerMock();
    }
    public function index_file($base_dir, $path) {
        $this->indexed_files[] = $path;
    }
}

class DBFactoryMock extends DBFactory {
    public $paths = array();
    public function build_index_db($path) {
        $this->paths[] = $path;
        return new NullDB();
    }
}

class NullDB implements Insert, Query {
    public function source_file($name, $content){return 0;}
    public function entity($type, $name, $file, $start_line, $end_line){return 0;}
    public function reference($type, $name, $file, $line){return 0;}
    public function get_reference($type, $name, $file, $line){return 0;}
    public function relation($name, $entity_id, $reference_id){return 0;}
    public function source_file_table() { return "source"; }
    public function entity_table() { return "entities"; }
    public function reference_table() { return "references"; }
    public function relations_table() { return "relations"; }
    public function builder() { throw new \RuntimeException("PANIC!"); }
}

class EngineTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->root = __DIR__."/data/src";
        $this->config = new Config(array(array
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
        $this->log = new LoggerMock();
        $this->db_factory = new DBFactoryMock();
        $this->indexer_factory = new IndexerFactoryMock();
        $this->analyzer_factory = new AnalyzerFactoryMock();
        $this->engine = new Engine
            ( $this->log
            , $this->config
            , $this->db_factory
            , $this->indexer_factory
            , $this->analyzer_factory
            );
    }

    public function test_smoke() {
        $this->engine->run(); 
        $this->assertTrue(true, "Engine ran successfully.");
    }

    public function test_calls_analyzer() {
        $this->engine->run();
        $this->assertTrue($this->analyzer_factory->analyzer_mocks[0]->run_called);
    }

    public function test_builds_index_db() {
        $this->engine->run();
        $expected = array($this->config->project_storage()."/index.sqlite");
        $this->assertEquals($expected, $this->db_factory->paths);
    }
}
