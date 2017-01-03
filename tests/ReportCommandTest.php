<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\ReportCommand;
use Lechimp\Dicto\App\Config;
use Lechimp\Dicto\Report;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

require_once(__DIR__."/tempdir.php");
require_once(__DIR__."/ReportMock.php");

class _ReportCommandTestConfig extends Config {
    public function __construct() {}
    public function set_reports($reports) {
        $this->reports = $reports;
        return $this;
    }
}


class ReportCommandTest extends PHPUnit_Framework_TestCase {
    public function test_execute() {
        $config_file_path = "/foo";
        $configs = array("/foo/a.yaml", "b.yaml", "c.yaml");
        $report_name = "foobar";
        $report = new Report\Config("/foo", "\\Foo\\Bar", "foobar.html", [], $report_name);
        $config = (new _ReportCommandTestConfig())
                    ->set_reports([$report]);

        $cmd_mock = $this
            ->getMockBuilder(ReportCommand::class)
            ->setMethods(array
                ( "load_config"
                , "build_dic"
                , "configure_runtime"
                ))
            ->getMock();

        $gen_mock = $this
            ->getMockBuilder(Report\Generator::class)
            ->disableOriginalConstructor()
            ->setMethods(array("generate"))
            ->getMock();

        $cmd_mock
            ->expects($this->at(0))
            ->method("load_config")
            ->with
                ( $this->equalTo($configs)
                )
            ->willReturn($config);

        $cmd_mock
            ->expects($this->at(1))
            ->method("build_dic")
            ->with
                ( $this->equalTo($config)
                )
            ->willReturn(array
                ( "report_generator" => $gen_mock
                ));

        $cmd_mock
            ->expects($this->at(2))
            ->method("configure_runtime")
            ->with
                ( $this->equalTo($config)
                );

        $gen_mock
            ->expects($this->at(0))
            ->method("generate")
            ->with
                ($this->equalTo($report->with_target("php://stdout"))
                );

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
        $inp_mock
            ->expects($this->at(1))
            ->method("getArgument")
            ->with
                ( $this->equalTo("name")
                )
            ->willReturn($report_name);

        $outp_mock = $this
            ->getMockBuilder(OutputInterface::class)
            ->getMock();

        $cmd_mock->execute($inp_mock, $outp_mock);
    }
}
