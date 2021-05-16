<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
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


class ReportCommandTest extends \PHPUnit\Framework\TestCase {
    public function test_execute() {
        $config_file_path = "/foo";
        $configs = array("/foo/a.yaml", "b.yaml", "c.yaml");
        $report_name = "foobar";
        $report = new Report\Config("/foo", "\\Foo\\Bar", "foobar.html", [], $report_name);
        $config = (new _ReportCommandTestConfig())
                    ->set_reports([$report]);

        $cmd = new ReportCommand;

        $gen_mock = $this
            ->getMockBuilder(Report\Generator::class)
            ->disableOriginalConstructor()
            ->setMethods(array("generate"))
            ->getMock();

        $dic = array
            ( "report_generator" => $gen_mock
            , "config" => $config
            );

        $gen_mock
            ->expects($this->once())
            ->method("generate")
            ->with
                ($this->equalTo($report->with_target("php://stdout"))
                );

        $inp_mock = $this
            ->getMockBuilder(InputInterface::class)
            ->getMock();
        $inp_mock
            ->expects($this->once())
            ->method("getArgument")
            ->with
                ( $this->equalTo("name")
                )
            ->willReturn($report_name);

        $outp_mock = $this
            ->getMockBuilder(OutputInterface::class)
            ->getMock();

        $cmd->pull_deps_from($dic);
        $cmd->execute($inp_mock, $outp_mock);
    }
}
