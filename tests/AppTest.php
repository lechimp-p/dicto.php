<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\App;
use Lechimp\Dicto\App\Config;
use Lechimp\Dicto\Indexer\IndexerFactory;
use Lechimp\Dicto\App\Engine;
use Lechimp\Dicto\Rules\Ruleset;

require_once(__DIR__."/tempdir.php");

class _App extends App {
    public function _create_dic($ruleset, $configs) {
        return $this->create_dic($ruleset, $configs);
    }

    public function _load_rules_file($path) {
        return $this->load_rules_file($path);
    }
}

class AppTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->app = new _App();
    }

    public function test_load_rules_file() {
        list($rs,$c) = $this->app->_load_rules_file(__DIR__."/data/rules.php");
        $this->assertInstanceOf(Ruleset::class, $rs);
        $this->assertInternalType("array", $c);
    }

    /**
     * @depends test_load_rules_file
     */
    public function test_dic_indexer_factory() {
        list($rs,$c) = $this->app->_load_rules_file(__DIR__."/data/rules.php");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic($rs, array($c));

        $this->assertInstanceOf(IndexerFactory::class, $dic["indexer_factory"]);
    }

    /**
     * @depends test_load_rules_file
     */
    public function test_dic_engine() {
        list($rs,$c) = $this->app->_load_rules_file(__DIR__."/data/rules.php");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic($rs, array($c));

        $this->assertInstanceOf(Engine::class, $dic["engine"]);
    }
}
