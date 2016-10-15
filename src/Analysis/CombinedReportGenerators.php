<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Analysis;

use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules\Rule;

/**
 * Combines multiple report generators into one.
 */
class CombinedReportGenerators implements ReportGenerator {
    /**
     * @var ReportGenerator[]
     */
    protected $generators;

    /**
     * @param   ReportGenerator[]
     */
    public function __construct(array $generators) {
        $this->generators = array_map(function(ReportGenerator $g) {
            return $g;
        }, $generators);
    }

    /**
     * @return  ReportGenerator[]
     */
    public function generators() {
        return $this->generators;
    }

    /**
     * @inheritdocs
     */
    public function begin_run($commit_hash) {
        foreach ($this->generators as $g) {
            $g->begin_run($commit_hash);
        }
    }

    /**
     * @inheritdocs
     */
    public function end_run() {
        foreach ($this->generators as $g) {
            $g->end_run();
        }
    }

    /**
     * @inheritdocs
     */
    public function begin_ruleset(Ruleset $ruleset) {
        foreach ($this->generators as $g) {
            $g->begin_ruleset($ruleset);
        }
    }

    /**
     * @inheritdocs
     */
    public function end_ruleset() {
        foreach ($this->generators as $g) {
            $g->end_ruleset();
        }
    }

    /**
     * @inheritdocs
     */
    public function begin_rule(Rule $rule) {
        foreach ($this->generators as $g) {
            $g->begin_rule($rule);
        }
    }

    /**
     * @inheritdocs
     */
    public function end_rule() {
        foreach ($this->generators as $g) {
            $g->end_rule();
        }
    }

    /**
     * @inheritdocs
     */
    public function report_violation(Violation $violation) {
        foreach ($this->generators as $g) {
            $g->report_violation($violation);
        }
    }
}
