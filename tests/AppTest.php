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
use Lechimp\Dicto\App\SourceStatus;
use Lechimp\Dicto\Rules\Ruleset;

require_once(__DIR__."/tempdir.php");

class _App extends App {
    public function _create_dic($ruleset, $configs) {
        return $this->create_dic($ruleset, $configs);
    }

    public function _load_rules_file($path) {
        return $this->load_rules_file($path);
    }

    public function _load_configs($path) {
        $arr = array();
        $this->load_configs(array($path), $arr);
        return $arr[0]; 
    }
}

class AppTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->app = new _App();
    }

    public function test_load_rules_file() {
        $rs = $this->app->_load_rules_file(__DIR__."/data/rules");
        $this->assertInstanceOf(Ruleset::class, $rs);
    }

    /**
     * @depends test_load_rules_file
     */
    public function test_dic_indexer_factory() {
        $rs = $this->app->_load_rules_file(__DIR__."/data/rules");
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic($rs, array($c));

        $this->assertInstanceOf(IndexerFactory::class, $dic["indexer_factory"]);
    }

    /**
     * @depends test_load_rules_file
     */
    public function test_dic_engine() {
        $rs = $this->app->_load_rules_file(__DIR__."/data/rules");
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic($rs, array($c));

        $this->assertInstanceOf(Engine::class, $dic["engine"]);
    }

    public function test_source_status() {
        $rs = $this->app->_load_rules_file(__DIR__."/data/rules");
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic($rs, array($c));

        $this->assertInstanceOf(SourceStatus::class, $dic["source_status"]);
    }
}
