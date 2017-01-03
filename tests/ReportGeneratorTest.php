<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Report\Generator;
use Lechimp\Dicto\Report\Config;
use Lechimp\Dicto\Report\DiffPerRuleReport;

require_once(__DIR__."/ReportMock.php");
require_once(__DIR__."/ReportTestBase.php");

class _Generator extends Generator {
    public function _maybe_load_source($path) {
        return $this->maybe_load_source($path);
    }
    public function _build_report($config) {
        return $this->build_report($config);
    }
    public function _open_handle($handle) {
        return $this->open_handle($handle);
    }
    public function _fully_qualified_class_name($handle) {
        return $this->fully_qualified_class_name($handle);
    }
}

class ReportGeneratorTest extends ReportTestBase {
    public function setUp() {
        parent::setUp();
        $this->gen = new _Generator($this->queries);
    }

    public function test_generate() {
        $data = ["foo" => "bar"];
        $temp_file = tempnam(sys_get_temp_dir(), 'dicto.php');
        $cfg = new Config("/foo", "ReportMock", $temp_file,
            ["data" => $data], "foo");
        $this->gen->generate($cfg);

        $output = file_get_contents($temp_file);

        $this->assertEquals(json_encode($data), $output);
    }

    public function test_maybe_load_source() {
        try {
            $this->gen->_maybe_load_source(__DIR__."/thistotallynotexists.php");
            $this->assertFalse("This should not happen.");
        }
        catch (\RuntimeException $e) {
            $this->assertNotInstanceOf
                ( \PHPUnit_Framework_ExpectationFailedException::class
                , $e
                );
        }

        $this->gen->_maybe_load_source(__DIR__."/data/SomeIncludeFileForReportGeneratorTest.php");
        $this->assertTrue(class_exists(ThisIsSomeClassThatWasIncludedInReportGeneratorTest::class));
    }

    public function test_build_report() {
        $report = $this->gen->_build_report(new Config("", "DiffPerRule", "", []));
        $this->assertInstanceOf(DiffPerRuleReport::class, $report);

        $report = $this->gen->_build_report(new Config("", "ReportMock", "", []));
        $this->assertInstanceOf(ReportMock::class, $report);
    }

    public function test_open_handle() {
        try {
            $this->gen->_open_handle("/");
            $this->assertFalse("This should not happen.");
        }
        catch (\RuntimeException $e) {
            $this->assertNotInstanceOf
                ( \PHPUnit_Framework_ExpectationFailedException::class
                , $e
                );
        }

        $handle = $this->gen->_open_handle(tempnam(sys_get_temp_dir(), 'dicto.php'));
        $this->assertTrue(is_resource($handle));

        $handle = $this->gen->_open_handle("php://stdout");
        $this->assertTrue(is_resource($handle));
    }

    public function test_fully_qualified_class_name() {
        $fq = $this->gen->_fully_qualified_class_name("\\Foo\\Bar");
        $this->assertEquals("\\Foo\\Bar", $fq);

        $fq = $this->gen->_fully_qualified_class_name("DiffPerRule");
        $this->assertEquals("\\".DiffPerRuleReport::class, $fq);

        $fq = $this->gen->_fully_qualified_class_name("DiffPerRuleReport");
        $this->assertEquals("\\".DiffPerRuleReport::class, $fq);

        $fq = $this->gen->_fully_qualified_class_name("Foo");
        $this->assertEquals("\\Foo", $fq);
    }
}
