<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Analysis\ReportGenerator;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Definition\Ruleset;
use Lechimp\Dicto\Rules\Rule;

class CLIReportGenerator implements ReportGenerator {
    /**
     * @inheritdoc
     */
    public function start_ruleset(Ruleset $rule) {
        echo "Result of analysis:\n\n";
    }

    /**
     * @inheritdoc
     */
    public function start_rule(Rule $rule) {
        echo "\n\n".$rule->pprint().":\n\n";
    }

    /**
     * @inheritdoc
     */
    public function report_violation(Violation $violation) {
        echo $violation->filename()." (".$violation->line_no().")\n";
    }
}
