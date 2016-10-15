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
    public function begin_run($commit_hash) {}
    public function end_run() {}
    public function begin_ruleset(Ruleset $rule) {}
    public function end_ruleset(Ruleset $rule) {}
    public function begin_rule(Rule $rule) {}
    public function end_rule(Rule $rule) {}
}
