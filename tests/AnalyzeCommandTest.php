<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\AnalyzeCommand;
use Lechimp\Dicto\App\Config;
use Lechimp\Dicto\App\Engine;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once(__DIR__."/tempdir.php");

class _AnalyzeCommandTestConfig extends Config {
    public function __construct() {}
}

class AnalyzeCommandTest extends \PHPUnit\Framework\TestCase {
    public function test_execute() {
        $cmd = new AnalyzeCommand();

        $engine_mock = $this
            ->getMockBuilder(Engine::class)
            ->disableOriginalConstructor()
            ->setMethods(array("run"))
            ->getMock();

        $dic = array("engine" => $engine_mock);

        $engine_mock
            ->expects($this->once())
            ->method("run");

        $inp_mock = $this
            ->getMockBuilder(InputInterface::class)
            ->getMock();

        $outp_mock = $this
            ->getMockBuilder(OutputInterface::class)
            ->getMock();

        $cmd->pull_deps_from($dic);
        $cmd->execute($inp_mock, $outp_mock);
    }
}
