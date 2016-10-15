<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Analysis\ReportGenerator;

use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules\Rule;

class ReportGeneratorMock implements ReportGenerator {
    public $violations = array();
    public function report_violation(Violation $violation) {
        $this->violations[] = $violation;
    }

    public $begin_run_called_with = false;
    public function begin_run($commit_hash) {
        $this->begin_run_called_with = $commit_hash;
    }

    public $end_run_called = false;
    public function end_run() {
        $this->end_run_called = true;
    }

    public $begin_ruleset_called_with = false;
    public function begin_ruleset(Ruleset $ruleset) {
        $this->begin_ruleset_called_with = $ruleset;
    }

    public $end_ruleset_called = false;
    public function end_ruleset() {
        $this->end_ruleset_called = true;
    }

    public $begin_rule_called_with = false;
    public function begin_rule(Rule $rule) {
        $this->begin_rule_called_with = $rule;
    }

    public $end_rule_called = false;
    public function end_rule() {
        $this->end_rule_called = true;
    }
}
