<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Indexer\Indexer;
use Lechimp\Dicto\Analysis\Analyzer;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Lechimp\Flightcontrol\Flightcontrol;
use Lechimp\Flightcontrol\File;
use Lechimp\Flightcontrol\FSObject;
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
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var Analyzer
     */
    protected $analyzer;

    public function __construct(Log $log, Config $config, Indexer $indexer, Analyzer $analyzer) {
        $this->log = $log;
        $this->config = $config;
        $this->indexer = $indexer;
        $this->analyzer = $analyzer;
    }

    /**
     * Run the analysis.
     * 
     * @return null
     */
    public function run() {
        $this->run_indexing();
        $this->run_analysis();
    }

    protected function run_indexing() {
        $fc = $this->init_flightcontrol();
        $fc->directory("/")
            ->recurseOn()
            ->filter(function(FSObject $obj) {
                foreach ($this->config->analysis_ignore() as $pattern) {
                    if (preg_match("%$pattern%", $obj->path()) !== 0) {
                        return false;
                    }
                }
                return true;
            })
            ->foldFiles(null, function($_, File $file) {
                $this->log->info("indexing: ".$file->path());
                $this->indexer->index_file($file->path());
            });
    }

    protected function run_analysis() {
        $this->analyzer->run();
    }

    /**
     * Initialize the filesystem abstraction.
     *
     * @return  Flightcontrol
     */
    public function init_flightcontrol() {
        $adapter = new Local($this->config->project_root(), LOCK_EX, Local::SKIP_LINKS);
        $flysystem = new Filesystem($adapter);
        return new Flightcontrol($flysystem);
    }
}
