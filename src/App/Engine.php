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
     * @var string
     */
    protected $project_root;

    /**
     * @var string[]
     */
    protected $ignore_patterns;

    /**
     * @var I\Indexer
     */
    protected $indexer;

    /**
     * @param   string  $project_root
     */
    public function __construct($project_root, array $ignore_patterns, I\Indexer $indexer) {
        assert('is_string($project_root)');
        $this->project_root = $project_root;
        // TODO: Check omit patterns.
        $this->ignore_patterns = $ignore_patterns;
        $this->indexer = $indexer;
    }

    /**
     * Run the analysis.
     * 
     * @return null
     */
    public function run() {
        $db = $this->init_database();
        $this->indexer->use_insert($db);
        $this->indexer->set_project_root_to($this->project_root);

        $fc = $this->init_flightcontrol();
        $fc->directory("/")
            ->recurseOn()
            ->filter(function(FSObject $obj) {
                foreach ($this->ignore_patterns as $pattern) {
                    if (preg_match("%$pattern%", $obj->path()) !== 0) {
                        return false;
                    }
                }
                return true;
            })
            ->foldFiles(null, function($_, File $file) {
                echo "indexing: ".$file->path()."\n";
                $this->indexer->index_file($file->path());
            });
    }

    /**
     * Initialize the database.
     * 
     * @return DB
     */
    protected function init_database() {
        $connection = DriverManager::getConnection
            ( array
                ( "driver" => "pdo_sqlite"
                , "path" => "/home/lechimp/Code/ILIAS.dicto.sqlite"
                )
            ); 

        // initialize regexp function for sqlite
        $pdo = $connection->getWrappedConnection();
        $pdo->sqliteCreateFunction("regexp", function($pattern, $data) {
            return preg_match("%$pattern%", $data) > 0;
        });

        $db = new DB($connection);
        $db->create_database();
        return $db;
    }

    /**
     * Initialize the filesystem abstraction.
     *
     * @return  Flightcontrol
     */
    public function init_flightcontrol() {
        $adapter = new Local($this->project_root, LOCK_EX, Local::SKIP_LINKS);
        $flysystem = new Filesystem($adapter);
        return new Flightcontrol($flysystem);
    }
}
