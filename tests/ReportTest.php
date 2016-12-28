<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Report\Report;

class ReportMock extends Report {
    public $data = [];

    public function __construct(array $config = []) {
        $this->config = $config;
    }

    protected function default_template_path() {
        return __DIR__."/../templates/json.php";
    }

    public function generate() {
        return $this->data;
    }

    public function _template_name($path) {
        return $this->template_name($path);
    }
}

class ReportTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->report = new ReportMock();
    }

    public function test_generate() {
        $data = ["foo" => "bar"];
        $this->report->data = $data;

        $this->assertSame($data, $this->report->generate());
    }

    public function test_template_name() {
        $this->assertEquals("json", $this->report->_template_name("json.php"));
        $this->assertEquals("json", $this->report->_template_name("/foo/bar/json.php"));
        $this->assertEquals("foobar", $this->report->_template_name("/foo/bar/foobar.php"));
    }

    public function test_write() {
        $data = ["foo" => "bar"];
        $this->report->data = $data;
        $handle = fopen("php://temp", "rw+");

        $this->report->write($handle);
        rewind($handle);

        $this->assertEquals(json_encode($data), stream_get_contents($handle));
    }
}
