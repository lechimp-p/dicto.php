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
use Lechimp\Dicto\App\RuleLoader;
use Lechimp\Dicto\App\SourceStatus;
use Lechimp\Dicto\Rules\Ruleset;

require_once(__DIR__."/tempdir.php");

class _App extends App {
    public function _create_dic($config_file_path, $rule_file_path, $configs) {
        return $this->create_dic($config_file_path, $rule_file_path, $configs);
    }

    public function _load_configs($path) {
        list($_, $c) = $this->load_configs(array($path));
        return $c[0];
    }
}

class AppTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->app = new _App();
    }

    public function test_dic_indexer_factory() {
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic("/the/path", __DIR__."/data/rules", array($c));

        $this->assertInstanceOf(IndexerFactory::class, $dic["indexer_factory"]);
    }

    public function test_dic_engine() {
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic("/the/path", __DIR__."/data/rules", array($c));

        $this->assertInstanceOf(Engine::class, $dic["engine"]);
    }

    public function test_source_status() {
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic("/the/path", __DIR__."/data/rules", array($c));

        $this->assertInstanceOf(SourceStatus::class, $dic["source_status"]);
    }

    public function test_run() {
        $config_file_path = "/foo";
        $configs = array("/foo/a.yaml", "b.yaml", "c.yaml");
        $rules_path = "rules.path";
        $params = array_merge(array("program_name", $rules_path), $configs);

        $app_mock = $this
            ->getMockBuilder(App::class)
            ->setMethods(array("load_configs", "load_rules_file", "create_dic"))
            ->getMock();

        $engine_mock = $this
            ->getMockBuilder(Engine::class)
            ->disableOriginalConstructor()
            ->setMethods(array("run"))
            ->getMock();

        $cfg_return = array("some_config");
        $app_mock
            ->expects($this->at(0))
            ->method("load_configs")
            ->with
                ( $this->equalTo($configs)
                )
            ->willReturn(array($config_file_path, $cfg_return));

        $app_mock
            ->expects($this->at(1))
            ->method("create_dic")
            ->with
                ( $this->equalTo($config_file_path)
                , $this->equalTo($rules_path)
                , $this->equalTo($cfg_return)
                )
            ->willReturn(array("engine" => $engine_mock));

        $engine_mock
            ->expects($this->at(0))
            ->method("run");

        $app_mock->run($params);
    }
}
