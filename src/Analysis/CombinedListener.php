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
 * Combines multiple listeners into one.
 */
class CombinedListener implements Listener {
    /**
     * @var Listener[]
     */
    protected $listeners;

    /**
     * @param   Listener[]  $listeners
     */
    public function __construct(array $listeners) {
        $this->listeners = array_map(function(Listener $l) {
            return $l;
        }, $listeners);
    }

    /**
     * @return  Listener[]
     */
    public function listeners() {
        return $this->listeners;
    }

    /**
     * @inheritdocs
     */
    public function begin_run($commit_hash) {
        foreach ($this->listeners as $g) {
            $g->begin_run($commit_hash);
        }
    }

    /**
     * @inheritdocs
     */
    public function end_run() {
        foreach ($this->listeners as $g) {
            $g->end_run();
        }
    }

    /**
     * @inheritdocs
     */
    public function begin_ruleset(Ruleset $ruleset) {
        foreach ($this->listeners as $g) {
            $g->begin_ruleset($ruleset);
        }
    }

    /**
     * @inheritdocs
     */
    public function end_ruleset() {
        foreach ($this->listeners as $g) {
            $g->end_ruleset();
        }
    }

    /**
     * @inheritdocs
     */
    public function begin_rule(Rule $rule) {
        foreach ($this->listeners as $g) {
            $g->begin_rule($rule);
        }
    }

    /**
     * @inheritdocs
     */
    public function end_rule() {
        foreach ($this->listeners as $g) {
            $g->end_rule();
        }
    }

    /**
     * @inheritdocs
     */
    public function report_violation(Violation $violation) {
        foreach ($this->listeners as $g) {
            $g->report_violation($violation);
        }
    }
}
