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

class _AnalysisCommandTestConfig extends Config {
    public function __construct() {}
}

class AnalysisCommandTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->command = new AnalysisCommand();

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

    public function test_execute() {
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
            ->willReturn(new _AnalysisCommandTestConfig());

        $cmd_mock
            ->expects($this->at(1))
            ->method("build_dic")
            ->with
                ( $this->equalTo(new _AnalysisCommandTestConfig())
                )
            ->willReturn(array("engine" => $engine_mock));

        $cmd_mock
            ->expects($this->at(2))
            ->method("configure_runtime")
            ->with
                ( $this->equalTo(new _AnalysisCommandTestConfig())
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
