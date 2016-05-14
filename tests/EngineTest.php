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
use Lechimp\Dicto\Indexer\Indexer;
use Lechimp\Dicto\App\Config;
use PhpParser\ParserFactory;
use Doctrine\DBAL\DriverManager;

class EngineTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->config = new Config(array(array
            ( "project" => array
                ( "root" => __DIR__."/data/src"
                )
            )));
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $connection = DriverManager::getConnection
            ( array
                ( "driver"  => "pdo_sqlite"
                , "memory"  => true
                )
            );

        $this->db = new Lechimp\Dicto\App\DB($connection);
        $this->db->maybe_init_database_schema();
        $this->db->init_sqlite_regexp();
        $this->indexer = new Indexer($parser, $this->config->project_root(), $this->db);
        $this->engine = new Engine($this->config, $this->indexer, $this->db);
    }

    public function test_smoke() {
        $this->engine->run(); 
        $this->assertTrue(true, "Engine ran successfully.");
    }

}
