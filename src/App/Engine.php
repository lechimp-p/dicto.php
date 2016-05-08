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

use Lechimp\Dicto\Indexer as I;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Lechimp\Flightcontrol\Flightcontrol;
use Lechimp\Flightcontrol\File;
use Lechimp\Flightcontrol\FSObject;
use Doctrine\DBAL\DriverManager;

/**
 * The Engine of the App drives the analysis process.
 */
class Engine {
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var I\Indexer
     */
    protected $indexer;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @param   string  $project_root
     */
    public function __construct(Config $config, I\Indexer $indexer, DB $db) {
        $this->config = $config;
        $this->indexer = $indexer;
        $this->db = $db;
    }

    /**
     * Run the analysis.
     * 
     * @return null
     */
    public function run() {
        $this->indexer->use_insert($this->db);
        $this->indexer->set_project_root_to($this->config->project_root());

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
                $this->indexer->index_file($file->path());
            });
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
