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
        $this->config = new Config(array(array
            ( "project" => array
                ( "root" => __DIR__."/data/src"
                )
            )));
        $this->indexer = new IndexerMock();
        $this->analyzer = new AnalyzerMock();
        $this->engine = new Engine($this->config, $this->indexer, $this->analyzer);
    }

    public function test_smoke() {
        $this->engine->run(); 
        $this->assertTrue(true, "Engine ran successfully.");
    }

}
