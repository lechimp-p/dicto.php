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

    public function test_template_name() {
        $this->assertEquals("template_json", $this->report->_template_function_name("json.php"));
        $this->assertEquals("template_json", $this->report->_template_function_name("/foo/bar/json.php"));
        $this->assertEquals("template_foobar", $this->report->_template_function_name("/foo/bar/foobar.php"));
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
        $report = new DiffPerRuleReport($this->queries, []);

        $handle = fopen("php://temp", "rw+");
        $report->write($handle);
        rewind($handle);

        $this->assertNotEmpty(stream_get_contents($handle));
    }
}
