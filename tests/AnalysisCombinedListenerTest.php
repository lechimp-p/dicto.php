<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Analysis\CombinedListener;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules\Rule;

require_once(__DIR__."/AnalysisListenerMock.php");

class AnalysisCombinedListenerGeneratorsTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->al1 = new AnalysisListenerMock();
        $this->al2 = new AnalysisListenerMock();
        $this->c = new CombinedListener([$this->al1, $this->al2]);
    }

    public function test_begin_run() {
        $this->c->begin_run("foo");

        $this->assertEquals("foo", $this->al1->begin_run_called_with);
        $this->assertEquals("foo", $this->al2->begin_run_called_with);
    }

    public function test_end_run() {
        $this->c->end_run();

        $this->assertTrue($this->al1->end_run_called);
        $this->assertTrue($this->al2->end_run_called);
    }

    public function test_begin_ruleset() {
        $rs = new Ruleset([],[]);
        $this->c->begin_ruleset($rs);

        $this->assertSame($rs, $this->al1->begin_ruleset_called_with);
        $this->assertSame($rs, $this->al2->begin_ruleset_called_with);
    }

    public function test_end_ruleset() {
        $this->c->end_ruleset();

        $this->assertTrue($this->al1->end_ruleset_called);
        $this->assertTrue($this->al2->end_ruleset_called);
    }

    public function test_begin_rule() {
        $r = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->c->begin_rule($r);

        $this->assertSame($r, $this->al1->begin_rule_called_with);
        $this->assertSame($r, $this->al2->begin_rule_called_with);
    }

    public function test_end_rule() {
        $this->c->end_rule();

        $this->assertTrue($this->al1->end_rule_called);
        $this->assertTrue($this->al2->end_rule_called);
    }

    public function test_report_violation() {
        $v = $this->getMockBuilder(Violation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->c->report_violation($v);

        $this->assertSame([$v], $this->al1->violations);
        $this->assertSame([$v], $this->al2->violations);
    }
}
