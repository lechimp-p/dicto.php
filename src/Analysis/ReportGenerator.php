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
 * Defines how the analysis talks to a report generator.
 */
interface ReportGenerator {
    /**
     * Tell the report generator that a run begins.
     *
     * @param   string  $commit_hash
     * @return  null
     */
    public function begin_run($commit_hash);

    /**
     * Tell the report generator that the run ended.
     *
     * @return null
     */
    public function end_run();

    /**
     * The analyzer will give violations on the given ruleset afterwards.
     *
     * @param   Ruleset     $ruleset
     * @return  null
     */
    public function begin_ruleset(Ruleset $ruleset);

    /**
     * The analyzer will give no more violations on the given ruleset afterwards.
     *
     * @return  null
     */
    public function end_ruleset();

    /**
     * The analyzer will give violations on the given rule afterwards.
     *
     * @param   Rule        $rule
     * @return  null
     */
    public function begin_rule(Rule $rule);

    /**
     * The analyzer will give no moew violations on the given rule afterwards.
     *
     * @return  null
     */
    public function end_rule();

    /**
     * The analyzer reports a violation on the rule previously given.
     *
     * @param   Violation   $violation
     * @return  null
     */
    public function report_violation(Violation $violation);
}
