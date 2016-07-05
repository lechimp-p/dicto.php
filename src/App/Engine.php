<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Indexer\IndexerFactory;
use Lechimp\Dicto\Analysis\AnalyzerFactory;
use Doctrine\DBAL\DriverManager;
use Psr\Log\LoggerInterface as Log;

/**
 * The Engine of the App drives the analysis process.
 */
class Engine {
    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var DBFactory
     */
    protected $db_factory;

    /**
     * @var IndexerFactory
     */
    protected $indexer_factory;

    /**
     * @var AnalyzerFactory
     */
    protected $analyzer_factory;

    /**
     * @var SourceStatus
     */
    protected $source_status;

    public function __construct( Log $log
                               , Config $config
                               , DBFactory $db_factory
                               , IndexerFactory $indexer_factory
                               , AnalyzerFactory $analyzer_factory
                               , SourceStatus $source_status
                               ) {
        $this->log = $log;
        $this->config = $config;
        $this->db_factory = $db_factory;
        $this->indexer_factory = $indexer_factory;
        $this->analyzer_factory = $analyzer_factory;
        $this->source_status = $source_status;
    }

    /**
     * Run the analysis.
     * 
     * @return null
     */
    public function run() {
        $index_db_path = $this->index_database_path();
        if (!$this->db_factory->index_db_exists($index_db_path)) {
            $index_db = $this->db_factory->build_index_db($index_db_path);
            $this->run_indexing($index_db);
        }
        else {
            $index_db = $this->db_factory->load_index_db($index_db_path);
        }
        $this->run_analysis($index_db);
    }

    protected function index_database_path() {
        $commit_hash = $this->source_status->commit_hash();
        return $this->config->project_storage()."/$commit_hash.sqlite";
    }

    protected function result_database_path() {
        return $this->config->project_storage()."/results.sqlite";
    }

    protected function run_indexing($db) {
        $indexer = $this->indexer_factory->build($db);
        $indexer->index_directory
            ( $this->config->project_root()
            , $this->config->analysis_ignore()
            );
    }

    protected function run_analysis($index_db) {
        $result_db = $this->db_factory->get_result_db($this->result_database_path());
        $analyzer = $this->analyzer_factory->build($index_db, $result_db);
        $analyzer->run();
    }
}
