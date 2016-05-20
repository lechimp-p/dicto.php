<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;
use Lechimp\Dicto\App\Engine;
use Lechimp\Dicto\Analysis\Analyzer;
use Lechimp\Dicto\Indexer\Indexer;
use Lechimp\Dicto\App\Config;
use PhpParser\ParserFactory;
use Doctrine\DBAL\DriverManager;
use Psr\Log\LogLevel;

require_once(__DIR__."/LoggerMock.php");

class AnalyzerMock extends Analyzer {
    public $run_called = false;
    public function __construct() {}
    public function run() {
        $this->run_called = true;
    }
}

class IndexerMock extends Indexer {
    public $indexed_files = array();
    public function __construct() {}
    public function index_file($path) {
        $this->indexed_files[] = $path;
    }
}

class EngineTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->root = __DIR__."/data/src";
        $this->config = new Config(array(array
            ( "project" => array
                ( "root" => $this->root
                )
            , "analysis" => array
                ( "ignore" => array
                    ( ".*\.omit_me"
                    )
                )
            )));
        $this->log = new LoggerMock();
        $this->indexer = new IndexerMock();
        $this->analyzer = new AnalyzerMock();
        $this->engine = new Engine($this->log, $this->config, $this->indexer, $this->analyzer);
    }

    public function test_smoke() {
        $this->engine->run(); 
        $this->assertTrue(true, "Engine ran successfully.");
    }

    public function test_calls_analyzer() {
        $this->engine->run();
        $this->assertTrue($this->analyzer->run_called);
    }

    public function test_indexes_files() {
        $this->engine->run();
        $expected = array_filter(scandir($this->root), function($n) {
            return $n != "." && $n != ".." && $n != "A1.omit_me";
        });

        $this->assertEquals(count($expected), count($this->indexer->indexed_files));
        foreach ($expected as $e) {
            $this->assertContains($e, $this->indexer->indexed_files);
        }
    }

    public function test_logging() {
        $this->engine->run();
        $expected_files = array_filter(scandir($this->root), function($n) {
            return $n != "." && $n != ".." && $n != "A1.omit_me";
        });

        foreach ($expected_files as $e) {
            $expected = array(LogLevel::INFO, "indexing: $e", array());
            $this->assertContains($expected, $this->log->log);
        }
    }
}
