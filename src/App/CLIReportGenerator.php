<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Analysis\ReportGenerator;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules\Rule;

class CLIReportGenerator implements ReportGenerator {
    protected $lines = array();

    protected function line($content = "") {
        $this->lines[] = $content;
    }

    /**
     * @inheritdoc
     */
    public function begin_run($commit_hash) {
    }

    /**
     * @inheritdoc
     */
    public function end_run() {
    }

    /**
     * @inheritdoc
     */
    public function begin_ruleset(Ruleset $rule) {
        $this->line("Result of analysis:");
        $this->line();
    }

    public function end_ruleset() {
        echo implode("\n", $this->lines);
    }

    /**
     * @inheritdoc
     */
    public function begin_rule(Rule $rule) {
        $this->line("------------------------------------------------------------------------------");
        $this->line($rule->pprint());
        $this->line("------------------------------------------------------------------------------");
        $this->line();
    }

    public function end_rule() {
        $this->line();
        $this->line();
    }

    /**
     * @inheritdoc
     */
    public function report_violation(Violation $violation) {
        $this->line($violation->filename()." (".$violation->line_no().")");
    }
}
