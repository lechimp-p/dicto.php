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
    public function _create_dic($config_file_path, $configs) {
        return $this->create_dic($config_file_path, $configs);
    }

    public function _load_configs($path) {
        list($_, $c) = $this->load_configs(array($path));
        return $c[0];
    }

    public function _load_schemas(array $schema_classes) {
        return $this->load_schemas($schema_classes);
    }

    public function _load_properties(array $property_classes) {
        return $this->load_properties($property_classes);
    }

    public function _load_variables(array $variable_classes) {
        return $this->load_variables($variable_classes);
    }

    public function _configure_runtime($config) {
        return $this->configure_runtime($config);
    }
}

class _Config extends Config {
    public function __construct() {}
}

class AppTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->app = new _App();
    }

    public function test_dic_indexer_factory() {
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic("/the/path", array($c));

        $this->assertInstanceOf(IndexerFactory::class, $dic["indexer_factory"]);
    }

    public function test_dic_engine() {
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic(__DIR__."/data", array($c));

        $this->assertInstanceOf(Engine::class, $dic["engine"]);
    }

    public function test_source_status() {
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic("/the/path", array($c));

        $this->assertInstanceOf(SourceStatus::class, $dic["source_status"]);
    }

    public function test_schemas() {
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic("/the/path", array($c));

        $expected_schemas = array
            ( new \Lechimp\Dicto\Rules\DependOn
            , new \Lechimp\Dicto\Rules\Invoke
            , new \Lechimp\Dicto\Rules\ContainText
            );
        $this->assertEquals($expected_schemas, $dic["schemas"]);
    }

    public function test_load_schemas() {
        $default_schemas =
            [ \Lechimp\Dicto\Rules\DependOn::class
            , \Lechimp\Dicto\Rules\Invoke::class
            , \Lechimp\Dicto\Rules\ContainText::class
            ];
        $schemas = $this->app->_load_schemas($default_schemas);
        $expected_schemas = array
            ( new \Lechimp\Dicto\Rules\DependOn
            , new \Lechimp\Dicto\Rules\Invoke
            , new \Lechimp\Dicto\Rules\ContainText
            );
        $this->assertEquals($expected_schemas, $schemas);
    }

    public function test_properties() {
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic("/the/path", array($c));

        $expected_properties =
            [ new \Lechimp\Dicto\Variables\Name
            , new \Lechimp\Dicto\Variables\In
            ];
        $this->assertEquals($expected_properties, $dic["properties"]);
    }

    public function test_load_properties() {
        $default_properties =
            [ \Lechimp\Dicto\Variables\Name::class
            , \Lechimp\Dicto\Variables\In::class
            ];
        $properties = $this->app->_load_properties($default_properties);
        $expected_properties =
            [ new \Lechimp\Dicto\Variables\Name
            , new \Lechimp\Dicto\Variables\In
            ];
        $this->assertEquals($expected_properties, $properties);
    }

    public function test_variables() {
        $c = $this->app->_load_configs(__DIR__."/data/base_config.yaml");
        $c["project"]["storage"] = tempdir();
        $dic = $this->app->_create_dic("/the/path", array($c));

        $expected_variables = array
            ( new \Lechimp\Dicto\Variables\Classes()
            , new \Lechimp\Dicto\Variables\Functions()
            , new \Lechimp\Dicto\Variables\Globals()
            , new \Lechimp\Dicto\Variables\Files()
            , new \Lechimp\Dicto\Variables\Methods()
            , new \Lechimp\Dicto\Variables\ErrorSuppressor()
            , new \Lechimp\Dicto\Variables\Exit_()
            , new \Lechimp\Dicto\Variables\Die_()
            );
        $this->assertEquals($expected_variables, $dic["variables"]);
    }

    public function test_load_variables() {
        $default_variables = array
            ( \Lechimp\Dicto\Variables\Classes::class
            , \Lechimp\Dicto\Variables\Functions::class
            , \Lechimp\Dicto\Variables\Globals::class
            , \Lechimp\Dicto\Variables\Files::class
            , \Lechimp\Dicto\Variables\Methods::class
            , \Lechimp\Dicto\Variables\ErrorSuppressor::class
            , \Lechimp\Dicto\Variables\Exit_::class
            , \Lechimp\Dicto\Variables\Die_::class
            );
        $variables = $this->app->_load_variables($default_variables);
        $expected_variables = array
            ( new \Lechimp\Dicto\Variables\Classes()
            , new \Lechimp\Dicto\Variables\Functions()
            , new \Lechimp\Dicto\Variables\Globals()
            , new \Lechimp\Dicto\Variables\Files()
            , new \Lechimp\Dicto\Variables\Methods()
            , new \Lechimp\Dicto\Variables\ErrorSuppressor()
            , new \Lechimp\Dicto\Variables\Exit_()
            , new \Lechimp\Dicto\Variables\Die_()
            );
        $this->assertEquals($expected_variables, $variables);
    }

    public function test_configure_runtime_1() {
        $active = assert_options(ASSERT_ACTIVE);
        $warning = assert_options(ASSERT_WARNING);
        $bail = assert_options(ASSERT_BAIL);

        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(["runtime_check_assertions"])
            ->getMock();
        $config->expects($this->once())
            ->method("runtime_check_assertions")
            ->willReturn(true);

        $this->app->_configure_runtime($config);

        $this->assertEquals(true, assert_options(ASSERT_ACTIVE));
        $this->assertEquals(true, assert_options(ASSERT_WARNING));
        $this->assertEquals(false, assert_options(ASSERT_BAIL));

        assert_options(ASSERT_ACTIVE, $active);
        assert_options(ASSERT_WARNING, $warning);
        assert_options(ASSERT_BAIL, $bail);
    }

    public function test_configure_runtime_2() {
        $active = assert_options(ASSERT_ACTIVE);
        $warning = assert_options(ASSERT_WARNING);
        $bail = assert_options(ASSERT_BAIL);

        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(["runtime_check_assertions"])
            ->getMock();
        $config->expects($this->once())
            ->method("runtime_check_assertions")
            ->willReturn(false);

        $this->app->_configure_runtime($config);

        $this->assertEquals(false, assert_options(ASSERT_ACTIVE));
        $this->assertEquals(false, assert_options(ASSERT_WARNING));
        $this->assertEquals(false, assert_options(ASSERT_BAIL));

        assert_options(ASSERT_ACTIVE, $active);
        assert_options(ASSERT_WARNING, $warning);
        assert_options(ASSERT_BAIL, $bail);
    }

    public function test_run() {
        $config_file_path = "/foo";
        $configs = array("/foo/a.yaml", "b.yaml", "c.yaml");
        $params = array_merge(array("program_name"), $configs);
        $default_schemas =
            [ "Lechimp\\Dicto\\Rules\\DependOn"
            , "Lechimp\\Dicto\\Rules\\Invoke"
            , "Lechimp\\Dicto\\Rules\\ContainText"
            ];

        $app_mock = $this
            ->getMockBuilder(App::class)
            ->setMethods(array
                ("load_configs"
                , "load_rules_file"
                , "create_dic"
                , "load_schemas"
                , "configure_runtime"
                ))
            ->getMock();

        $engine_mock = $this
            ->getMockBuilder(Engine::class)
            ->disableOriginalConstructor()
            ->setMethods(array("run"))
            ->getMock();

        $cfg_return = ["some_config"];
        $app_mock
            ->expects($this->at(0))
            ->method("load_configs")
            ->with
                ( $this->equalTo($configs)
                )
            ->willReturn(array($config_file_path."/a.yaml", $cfg_return));

        $app_mock
            ->expects($this->at(1))
            ->method("create_dic")
            ->with
                ( $this->equalTo($config_file_path)
                , $this->equalTo($cfg_return)
                )
            ->willReturn(array("engine" => $engine_mock, "config" => new _Config()));

        $app_mock
            ->expects($this->at(2))
            ->method("configure_runtime")
            ->with
                ( $this->equalTo(new _Config())
                );

        $engine_mock
            ->expects($this->at(0))
            ->method("run");

        $app_mock->run($params);
    }
}
