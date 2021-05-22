<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Indexer\InsertTwice;
use Lechimp\Dicto\Indexer\IndexerFactory;
use Lechimp\Dicto\Analysis\Listener;
use Lechimp\Dicto\Analysis\AnalyzerFactory;
use Lechimp\Dicto\Analysis\Index;
use Lechimp\Dicto\DB;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Graph;
use Psr\Log\LoggerInterface as Log;

/**
 * The Engine of the App drives the analysis process.
 */
class Engine
{
    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var DB\IndexDBFactory
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
     * @var Listener
     */
    protected $analysis_listener;

    /**
     * @var SourceStatus
     */
    protected $source_status;

    public function __construct(
        Log $log,
        Config $config,
        DB\IndexDBFactory $db_factory,
        IndexerFactory $indexer_factory,
        AnalyzerFactory $analyzer_factory,
        Listener $analysis_listener,
        SourceStatus $source_status
    ) {
        $this->log = $log;
        $this->config = $config;
        $this->db_factory = $db_factory;
        $this->indexer_factory = $indexer_factory;
        $this->analyzer_factory = $analyzer_factory;
        $this->analysis_listener = $analysis_listener;
        $this->source_status = $source_status;
    }

    /**
     * Run the analysis.
     *
     * @return null
     */
    public function run()
    {
        $index_db_path = $this->index_database_path();
        if (!$this->db_factory->index_db_exists($index_db_path)) {
            $index = $this->build_index();
            $this->run_indexing($index);
            if ($index instanceof InsertTwice) {
                $index_db = $this->force_app_index_db($index->second());
                $index_db->write_cached_inserts();
                $index = $this->force_graph_index_db($index->first());
            }
        } else {
            $index_db = $this->db_factory->load_index_db($index_db_path);
            $this->log->notice("Reading index from database '$index_db_path'...");
            $index = $this->read_index_from($index_db);
        }
        $this->run_analysis($index);
    }

    protected function index_database_path()
    {
        $commit_hash = $this->source_status->commit_hash();
        return $this->config->project_storage() . "/$commit_hash.sqlite";
    }

    protected function run_indexing(Insert $index)
    {
        $this->log->notice("Building index...");
        $indexer = $this->indexer_factory->build($index);
        $this->with_time_measurement(function ($s) {
                return "Indexing took $s seconds to run.";
            }, function () use ($indexer) {
                $indexer->index_directory(
                        $this->config->project_root(),
                        $this->config->analysis_ignore()
                    );
            });
    }

    protected function run_analysis(Index $index)
    {
        $this->log->notice("Running analysis...");
        $commit_hash = $this->source_status->commit_hash();
        $this->analysis_listener->begin_run($commit_hash);
        $analyzer = $this->analyzer_factory->build($index, $this->analysis_listener);
        $this->with_time_measurement(function ($s) {
                return "Analysis took $s seconds to run.";
            }, function () use ($analyzer) {
                $analyzer->run();
            });
        $this->analysis_listener->end_run();
    }

    protected function build_index()
    {
        if ($this->config->analysis_store_index()) {
            $index_db_path = $this->index_database_path();
            $index_db = $this->db_factory->build_index_db($index_db_path);
            $this->log->notice("Writing index to database '$index_db_path'...");
            return new InsertTwice(new Graph\IndexDB, $index_db);
        }

        return new Graph\IndexDB;
    }

    /**
     * @return  Graph\IndexDB
     */
    protected function read_index_from(DB\IndexDB $db)
    {
        $index = null;
        $this->with_time_measurement(function ($s) {
                return "Loading the index took $s seconds.";
            }, function () use ($db, &$index) {
                $index = $db->to_graph_index();
            });
        return $this->force_graph_index_db($index);
    }

    protected function with_time_measurement(\Closure $message, \Closure $what)
    {
        $start_time = microtime(true);
        $what();
        $time_elapsed_secs = microtime(true) - $start_time;
        $this->log->notice($message($time_elapsed_secs));
    }

    /**
     * @param   mixed   $index
     * @return  Graph\IndexDB
     */
    protected function force_graph_index_db($index)
    {
        if ($index instanceof Graph\IndexDB) {
            return $index;
        } else {
            throw new \LogicException(
                "Expected index to be of type Graph\IndexDB, but it is '" .
                get_class($index) . "'"
            );
        }
    }

    /**
     * @param   mixed   $index
     * @return  IndexDB
     */
    protected function force_app_index_db($index)
    {
        if ($index instanceof DB\IndexDB) {
            return $index;
        } else {
            throw new \LogicException(
                "Expected index to be of type App\IndexDB, but it is '" .
                get_class($index) . "'"
            );
        }
    }
}
