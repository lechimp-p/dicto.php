<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Analysis\Listener;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules\Rule;

# TODO: Rename this or move this to Report namespace.
class CLIReportGenerator implements Listener
{
    protected $lines = array();
    protected $current_rule = null;
    protected $current_violations = [];

    protected function line($content = "")
    {
        $this->lines[] = $content;
    }

    /**
     * @inheritdoc
     */
    public function begin_run($commit_hash)
    {
    }

    /**
     * @inheritdoc
     */
    public function end_run()
    {
    }

    /**
     * @inheritdoc
     */
    public function begin_ruleset(Ruleset $rule)
    {
        $this->line("Result of analysis:");
        $this->line();
    }

    public function end_ruleset()
    {
        echo implode("\n", $this->lines);
    }

    /**
     * @inheritdoc
     */
    public function begin_rule(Rule $rule)
    {
        $this->current_rule = $rule;
        $this->current_violations = [];
    }

    public function end_rule()
    {
        $this->line("################################################################################");
        $this->line();
        $this->line(" " . $this->current_rule->pprint());
        $this->line(" -> " . count($this->current_violations) . " Violations");
        $this->line();
        $this->line("################################################################################");
        $this->line();

        foreach ($this->current_violations as $violation) {
            $this->line($violation->filename() . " (" . $violation->line_no() . "): ");
            $this->line("    " . trim($violation->line()));
            $this->line();
        }

        $this->line();
        $this->line();
    }

    /**
     * @inheritdoc
     */
    public function report_violation(Violation $violation)
    {
        $this->current_violations[] = $violation;
    }
}
