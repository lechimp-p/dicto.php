<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\App;
use Lechimp\Dicto\App\Command;
use Lechimp\Dicto\App\Config;
use Lechimp\Dicto\App\DIC;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

require_once(__DIR__."/tempdir.php");

class _Command extends Command {
    public $dic = null;
    public $executed = false;

    public function pull_deps_from($dic) {
        $this->dic = $dic;
    }

    public function configure() {
        $this->setName("test");
        $this->addArgument
            ( "configs"
            , InputArgument::IS_ARRAY
            , "Give paths to config files, separated by spaces."
            );
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $this->executed = true;
    }
}

class _Command2 extends Command {
    public $dic = null;
    public $executed = false;

    public function pull_deps_from($dic) {
        $this->dic = $dic;
    }

    public function configure() {
        $this->setName("test2");
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $this->executed = true;
    }
}

class _App extends App {
    public $command = null;

    protected function add_commands() {
        $this->command = new _Command();
        $this->add($this->command);
        $this->command2 = new _Command2();
        $this->add($this->command2);
    }

    public function _load_config(array $paths) {
        return $this->load_config($paths);
    }

    public function _configure_runtime($config) {
        return $this->configure_runtime($config);
    }

    public function _build_dic($config) {
        return $this->build_dic($config);
    }
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

    public function test_build_dic() {
        $dic = $this->app->_build_dic($this->config);
        $this->assertInstanceOf(DIC::class, $dic);
    }

    public function test_run() {
        $configs = ["config1.yml", "config2.yml"];

        $app_mock = $this
            ->getMockBuilder(_App::class)
            ->setMethods
                (["configure_runtime"
                , "load_config"
                , "build_dic"
                ])
            ->getMock();

        $app_mock
            ->expects($this->at(0))
            ->method("load_config")
            ->with($this->equalTo($configs))
            ->willReturn($this->config);

        $app_mock
            ->expects($this->at(1))
            ->method("configure_runtime")
            ->with($this->equalTo($this->config));

        $dic = ["this is the" => "dic"];

        $app_mock
            ->expects($this->at(2))
            ->method("build_dic")
            ->with($this->equalTo($this->config))
            ->willReturn($dic);

        $app_mock->setAutoExit(false);

        $input = new ArrayInput
            (["command" => "test"
            , "configs" => $configs
            ]);
        $output = new NullOutput();

        $app_mock->run($input, $output);

        $this->assertTrue($app_mock->command->executed);
        $this->assertEquals($dic, $app_mock->command->dic);
    }

    public function test_run_no_config() {
        $app_mock = $this
            ->getMockBuilder(_App::class)
            ->setMethods
                (["configure_runtime"
                , "load_config"
                , "build_dic"
                ])
            ->getMock();

        $app_mock->command->has_args = true;

        $app_mock
            ->expects($this->never())
            ->method("load_config");

        $app_mock
            ->expects($this->never())
            ->method("configure_runtime");

        $app_mock
            ->expects($this->never())
            ->method("build_dic");

        $app_mock->setAutoExit(false);

        $input = new ArrayInput
            (["command" => "test2"
            ]);
        $output = new NullOutput();

        $app_mock->run($input, $output);

        $this->assertTrue($app_mock->command2->executed);
        $this->assertEquals(null, $app_mock->command2->dic);
    }
}
