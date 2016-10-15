<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Analysis\CombinedReportGenerators;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules\Rule;

require_once(__DIR__."/ReportGeneratorMock.php");

class CombinedReportGeneratorsTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->rp1 = new ReportGeneratorMock();
        $this->rp2 = new ReportGeneratorMock();
        $this->c = new CombinedReportGenerators([$this->rp1, $this->rp2]);
    }

    public function test_begin_run() {
        $this->c->begin_run("foo");

        $this->assertEquals("foo", $this->rp1->begin_run_called_with);
        $this->assertEquals("foo", $this->rp2->begin_run_called_with);
    }

    public function test_end_run() {
        $this->c->end_run();

        $this->assertTrue($this->rp1->end_run_called);
        $this->assertTrue($this->rp2->end_run_called);
    }

    public function test_begin_ruleset() {
        $rs = new Ruleset([],[]);
        $this->c->begin_ruleset($rs);

        $this->assertSame($rs, $this->rp1->begin_ruleset_called_with);
        $this->assertSame($rs, $this->rp2->begin_ruleset_called_with);
    }

    public function test_end_ruleset() {
        $this->c->end_ruleset();

        $this->assertTrue($this->rp1->end_ruleset_called);
        $this->assertTrue($this->rp2->end_ruleset_called);
    }

    public function test_begin_rule() {
        $r = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->c->begin_rule($r);

        $this->assertSame($r, $this->rp1->begin_rule_called_with);
        $this->assertSame($r, $this->rp2->begin_rule_called_with);
    }

    public function test_end_rule() {
        $this->c->end_rule();

        $this->assertTrue($this->rp1->end_rule_called);
        $this->assertTrue($this->rp2->end_rule_called);
    }

    public function test_report_violation() {
        $v = $this->getMockBuilder(Violation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->c->report_violation($v);

        $this->assertSame([$v], $this->rp1->violations);
        $this->assertSame([$v], $this->rp2->violations);
    }
}
