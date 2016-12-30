<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\AnalysisCommand;
use Lechimp\Dicto\App\Config;
use Lechimp\Dicto\App\Engine;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once(__DIR__."/tempdir.php");

class _AnalysisCommand extends AnalysisCommand {
    public function _load_config(array $paths) {
        return $this->load_config($paths);
    }

    public function _configure_runtime($config) {
        return $this->configure_runtime($config);
    }
}

class __Config extends Config {
    public function __construct() {}
}

class AnalysisCommandTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->command = new _AnalysisCommand();

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

        $this->command->_configure_runtime($config);

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

        $this->command->_configure_runtime($config);

        $this->assertEquals(false, assert_options(ASSERT_ACTIVE));
        $this->assertEquals(false, assert_options(ASSERT_WARNING));
        $this->assertEquals(false, assert_options(ASSERT_BAIL));

        assert_options(ASSERT_ACTIVE, $active);
        assert_options(ASSERT_WARNING, $warning);
        assert_options(ASSERT_BAIL, $bail);
    }

    public function test_load_config() {
        $config = $this->command->_load_config(
            [ __DIR__."/data/base_config.yaml"
            , __DIR__."/data/additional_config.yaml"
            ]);
        $this->assertInstanceOf(Config::class, $config);
    }

    public function test_run() {
        $config_file_path = "/foo";
        $configs = array("/foo/a.yaml", "b.yaml", "c.yaml");
        $default_schemas =
            [ "Lechimp\\Dicto\\Rules\\DependOn"
            , "Lechimp\\Dicto\\Rules\\Invoke"
            , "Lechimp\\Dicto\\Rules\\ContainText"
            ];

        $cmd_mock = $this
            ->getMockBuilder(AnalysisCommand::class)
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

        $cmd_mock
            ->expects($this->at(0))
            ->method("load_config")
            ->with
                ( $this->equalTo($configs)
                )
            ->willReturn(new __Config());

        $cmd_mock
            ->expects($this->at(1))
            ->method("build_dic")
            ->with
                ( $this->equalTo(new __Config())
                )
            ->willReturn(array("engine" => $engine_mock));

        $cmd_mock
            ->expects($this->at(2))
            ->method("configure_runtime")
            ->with
                ( $this->equalTo(new __Config())
                );

        $engine_mock
            ->expects($this->at(0))
            ->method("run");

        $inp_mock = $this
            ->getMockBuilder(InputInterface::class)
            ->getMock();
        $inp_mock
            ->expects($this->at(0))
            ->method("getArgument")
            ->with
                ( $this->equalTo("configs")
                )
            ->willReturn($configs);

        $outp_mock = $this
            ->getMockBuilder(OutputInterface::class)
            ->getMock();

        $cmd_mock->execute($inp_mock, $outp_mock);
    }
}
