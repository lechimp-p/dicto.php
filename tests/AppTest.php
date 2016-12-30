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
use Lechimp\Dicto\App\Engine;

require_once(__DIR__."/tempdir.php");

class _App extends App {
    public function _load_config(array $paths) {
        return $this->load_config($paths);
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

        $config_params =
            [ "project" =>
                [ "root" => "./src"
                , "rules" => "./rules"
                , "storage" => tempdir()
                ]
            , "analysis" =>
                [ "ignore" =>
                    [ ".*\\.omit_me"
                    ]
                ]
            ];
        $this->config = new Config(__DIR__."/data", [$config_params]);
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

    public function test_load_config() {
        $config = $this->app->_load_config(
            [ __DIR__."/data/base_config.yaml"
            , __DIR__."/data/additional_config.yaml"
            ]);
        $this->assertInstanceOf(Config::class, $config);
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
                ( "load_config"
                , "build_dic"
                , "configure_runtime"
                ))
            ->getMock();

        $engine_mock = $this
            ->getMockBuilder(Engine::class)
            ->disableOriginalConstructor()
            ->setMethods(array("run"))
            ->getMock();

        $app_mock
            ->expects($this->at(0))
            ->method("load_config")
            ->with
                ( $this->equalTo($configs)
                )
            ->willReturn(new _Config());

        $app_mock
            ->expects($this->at(1))
            ->method("build_dic")
            ->with
                ( $this->equalTo(new _Config())
                )
            ->willReturn(array("engine" => $engine_mock));

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
