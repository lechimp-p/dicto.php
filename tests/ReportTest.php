<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Report\DiffPerRuleReport;

require_once(__DIR__."/ReportMock.php");
require_once(__DIR__."/ReportTestBase.php");

class ReportTest extends ReportTestBase {
    public function setUp() {
        $this->report = new ReportMock();
    }

    public function test_generate() {
        $data = ["foo" => "bar"];
        $this->report->data = $data;

        $this->assertSame($data, $this->report->generate());
    }

    public function test_template_function_name() {
        $this->assertEquals("template_json", $this->report->_template_function_name("json.php"));
        $this->assertEquals("template_json", $this->report->_template_function_name("/foo/bar/json.php"));
        $this->assertEquals("template_foobar", $this->report->_template_function_name("/foo/bar/foobar.php"));
    }

    public function test_template_path() {
        $this->assertEquals
                ( realpath(__DIR__."/../templates/json.php")
                , $this->report->_template_path("json")
                );
        $this->assertEquals
                ( realpath(__DIR__."/../templates/json.php")
                , $this->report->_template_path("json")
                );
        $this->assertEquals
                ( realpath(__DIR__."/../dicto.php")
                , $this->report->_template_path("dicto.php")
                );
        $this->assertEquals
                ( realpath(__FILE__)
                , $this->report->_template_path(__FILE__)
                );
        // Since ReportMock.path == __DIR__:
        $this->assertEquals
                ( realpath(__FILE__)
                , $this->report->_template_path("ReportTest.php")
                );
        try {
            $this->report->_template_path(__DIR__."/foo.bar");
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_write() {
        $data = ["foo" => "bar"];
        $this->report->data = $data;
        $handle = fopen("php://temp", "rw+");

        $this->report->write($handle);
        rewind($handle);

        $this->assertEquals(json_encode($data), stream_get_contents($handle));
    }

    public function test_diff_per_rule_smoke() {
        parent::setUp();
        $this->init_scenario();
        $report = new DiffPerRuleReport($this->queries, new ReportConfigMock([]));

        $handle = fopen("php://temp", "rw+");
        $report->write($handle);
        rewind($handle);

        $this->assertNotEmpty(stream_get_contents($handle));
    }
}
