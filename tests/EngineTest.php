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
use Lechimp\Dicto\App\Config;
use PhpParser\ParserFactory;

class EngineTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->config = new Config(array(array
            ( "project" => array
                ( "root" => __DIR__."/data/src"
                )
            )));
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->indexer = new Lechimp\Dicto\Indexer\PhpParser\Indexer($parser);
        $this->engine = new Engine($this->config, $this->indexer);
    }

    public function test_smoke() {
        $this->engine->run(); 
        $this->assertTrue(true, "Engine ran successfully.");
    }

}
