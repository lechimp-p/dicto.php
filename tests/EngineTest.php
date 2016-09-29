<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Analysis\Analyzer;
use Lechimp\Dicto\Analysis\AnalyzerFactory;
use Lechimp\Dicto\Analysis\Index;
use Lechimp\Dicto\Analysis\ReportGenerator;
use Lechimp\Dicto\App\Config;
use Lechimp\Dicto\App\DBFactory;
use Lechimp\Dicto\App\Engine;
use Lechimp\Dicto\App\SourceStatus;
use Lechimp\Dicto\Indexer\Indexer;
use Lechimp\Dicto\Indexer\IndexerFactory;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Rules\RuleSet;
use PhpParser\ParserFactory;
use Doctrine\DBAL\DriverManager;
use Psr\Log\LogLevel;

require_once(__DIR__."/LoggerMock.php");
require_once(__DIR__."/tempdir.php");
require_once(__DIR__."/NullDB.php");

class AnalyzerFactoryMock extends AnalyzerFactory {
    public $analyzer_mocks = array();
    public function __construct() {}
    public function build(Index $index, ReportGenerator $report_generator) {
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
    public $build_paths = array();
    public $load_paths = array();
    public $index_db_exists = false;
    public function build_index_db($path) {
        $this->build_paths[] = $path;
        return new NullDB();
    }
    public function load_index_db($path) {
        $this->load_paths[] = $path;
        return new NullDB();
    }
    public function index_db_exists($path) {
        return $this->index_db_exists; 
    }
}

class SourceStatusMock implements SourceStatus {
    public $commit_hash = "commit_hash";
    public function commit_hash() {
        return $this->commit_hash;
    }
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
        $this->source_status = new SourceStatusMock();
        $this->engine = new Engine
            ( $this->log
            , $this->config
            , $this->db_factory
            , $this->indexer_factory
            , $this->analyzer_factory
            , $this->source_status
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
        $commit_hash = uniqid();
        $this->source_status->commit_hash = $commit_hash;

        $this->engine->run();

        $expected = array($this->config->project_storage()."/$commit_hash.sqlite");
        $this->assertEquals($expected, $this->db_factory->build_paths);
        $this->assertEquals(array(), $this->db_factory->load_paths);
    }

    public function test_no_reindex_on_existing_index_db() {
        $commit_hash = uniqid();
        $this->source_status->commit_hash = $commit_hash;
        $this->db_factory->index_db_exists = true;

        $this->engine->run();

        $this->assertEquals(array(), $this->db_factory->build_paths); 
        $expected = array($this->config->project_storage()."/$commit_hash.sqlite");
        $this->assertEquals($expected, $this->db_factory->load_paths); 
        $this->assertEquals(array(), $this->indexer_factory->indexer_mocks);
    }
}
