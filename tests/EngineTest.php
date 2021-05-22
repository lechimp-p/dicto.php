<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Analysis\Analyzer;
use Lechimp\Dicto\Analysis\AnalyzerFactory;
use Lechimp\Dicto\Analysis\Index;
use Lechimp\Dicto\Analysis\Listener;
use Lechimp\Dicto\App\Config;
use Lechimp\Dicto\App\Engine;
use Lechimp\Dicto\App\SourceStatus;
use Lechimp\Dicto\DB;
use Lechimp\Dicto\Graph;
use Lechimp\Dicto\Indexer\Indexer;
use Lechimp\Dicto\Indexer\IndexerFactory;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Rules\RuleSet;
use Doctrine\DBAL\DriverManager;
use Psr\Log\LogLevel;

require_once(__DIR__ . "/AnalysisListenerMock.php");
require_once(__DIR__ . "/LoggerMock.php");
require_once(__DIR__ . "/tempdir.php");
require_once(__DIR__ . "/NullDB.php");

class AnalyzerFactoryMock extends AnalyzerFactory
{
    public $analyzer_mocks = array();
    public $analysis_listeners = array();
    public function __construct()
    {
    }
    public function build(Index $index, Listener $analysis_listener)
    {
        $analyzer_mock = new AnalyzerMock();
        $this->analyzer_mocks[] = $analyzer_mock;
        $this->analysis_listeners[] = $analysis_listener;
        return $analyzer_mock;
    }
}

class AnalyzerMock extends Analyzer
{
    public $run_called = false;
    public function __construct()
    {
    }
    public function run()
    {
        $this->run_called = true;
    }
}

class IndexerFactoryMock extends IndexerFactory
{
    public $indexer_mocks = array();
    public function __construct()
    {
    }
    public function build(Insert $insert)
    {
        $indexer_mock = new IndexerMock();
        $this->indexer_mocks[] = $indexer_mock;
        return $indexer_mock;
    }
}

class IndexerMock extends Indexer
{
    public $indexed_files = array();
    public function __construct()
    {
        $this->log = new LoggerMock();
    }
    public function index_file($base_dir, $path)
    {
        $this->indexed_files[] = $path;
    }
}

class __IndexDB extends NullDB
{
    public $write_cached_inserts_called = false;
    public function write_cached_inserts()
    {
        $this->write_cached_inserts_called = true;
    }
}

class IndexDBFactoryMock extends DB\IndexDBFactory
{
    public $build_paths = array();
    public $load_paths = array();
    public $index_db_exists = false;
    public $index_db = null;
    public function build_index_db($path)
    {
        $this->build_paths[] = $path;
        $this->index_db = new __IndexDB();
        return $this->index_db;
    }
    public function load_index_db($path)
    {
        $this->load_paths[] = $path;
        $this->index_db = new __IndexDB();
        return $this->index_db;
    }
    public function index_db_exists($path)
    {
        return $this->index_db_exists;
    }
}

class SourceStatusMock implements SourceStatus
{
    public $commit_hash = "commit_hash";
    public function commit_hash()
    {
        return $this->commit_hash;
    }
}

class _Engine extends Engine
{
    public $read_index_from_called = false;
    public function read_index_from(DB\IndexDB $db)
    {
        $this->read_index_from_called = true;
        return new Graph\IndexDB();
    }
    public function _read_index_from(DB\IndexDB $db)
    {
        return parent::read_index_from($db);
    }
}

class EngineTest extends \PHPUnit\Framework\TestCase
{
    public function setUp() : void
    {
        $this->build_engine_with_store_index_set_to(false);
    }

    protected function build_engine_with_store_index_set_to($value)
    {
        $this->root = __DIR__ . "/data/src";
        $this->config = new Config(__DIR__ . "/data", array(array( "project" => array( "root" => $this->root
                , "storage" => tempdir()
                , "rules" => "./rules"
                )
            , "analysis" => array( "ignore" => array( ".*\.omit_me"
                    )
                , "store_index" => $value
                )
            )));
        $this->log = new LoggerMock();
        $this->db_factory = new IndexDBFactoryMock();
        $this->indexer_factory = new IndexerFactoryMock();
        $this->analyzer_factory = new AnalyzerFactoryMock();
        $this->analysis_listener = new AnalysisListenerMock();
        $this->source_status = new SourceStatusMock();
        $this->engine = new _Engine(
                $this->log,
                $this->config,
                $this->db_factory,
                $this->indexer_factory,
                $this->analyzer_factory,
                $this->analysis_listener,
                $this->source_status
            );
    }

    public function test_smoke()
    {
        $this->engine->run();
        $this->assertTrue(true, "Engine ran successfully.");
    }

    public function test_calls_analyzer()
    {
        $this->engine->run();
        $this->assertTrue($this->analyzer_factory->analyzer_mocks[0]->run_called);
    }

    public function test_does_not_build_index_db_when_no_store_index()
    {
        $this->engine->run();

        $this->assertEquals(array(), $this->db_factory->build_paths);
        $this->assertEquals(array(), $this->db_factory->load_paths);
    }

    public function test_builds_index_db()
    {
        $this->build_engine_with_store_index_set_to(true);

        $commit_hash = uniqid();
        $this->source_status->commit_hash = $commit_hash;

        $this->engine->run();

        $expected = array($this->config->project_storage() . "/$commit_hash.sqlite");
        $this->assertEquals($expected, $this->db_factory->build_paths);
        $this->assertEquals(array(), $this->db_factory->load_paths);
        $this->assertTrue($this->db_factory->index_db->write_cached_inserts_called);
    }

    public function test_no_reindex_on_existing_index_db()
    {
        $commit_hash = uniqid();
        $this->source_status->commit_hash = $commit_hash;
        $this->db_factory->index_db_exists = true;

        $this->engine->run();

        $this->assertEquals(array(), $this->db_factory->build_paths);
        $expected = array($this->config->project_storage() . "/$commit_hash.sqlite");
        $this->assertEquals($expected, $this->db_factory->load_paths);
        $this->assertEquals(array(), $this->indexer_factory->indexer_mocks);
        $this->assertTrue($this->engine->read_index_from_called);
    }

    public function test_call_begin_run_on_analysis_listener()
    {
        $commit_hash = uniqid();
        $this->source_status->commit_hash = $commit_hash;

        $this->engine->run();

        $this->assertEquals($commit_hash, $this->analysis_listener->begin_run_called_with);
    }

    public function test_call_end_run_on_analysis_listener()
    {
        $this->engine->run();

        $this->assertTrue($this->analysis_listener->end_run_called);
    }

    public function test_passes_analysis_listener_to_analyzer_factory()
    {
        $this->engine->run();

        $this->assertSame(
                $this->analyzer_factory->analysis_listeners[0],
                $this->analysis_listener
            );
    }
}
