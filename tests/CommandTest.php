<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\Command;
use Lechimp\Dicto\App\Config;
use Lechimp\Dicto\App\Engine;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once(__DIR__."/tempdir.php");

class _Command extends Command {
    /**
     * @inheritdoc
     */
    public function configure() {
        $this
            ->setName("test");
    }

    public function _load_config(array $paths) {
        return $this->load_config($paths);
    }

    public function _configure_runtime($config) {
        return $this->configure_runtime($config);
    }
}

class CommandTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->command = new _Command();

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
}
