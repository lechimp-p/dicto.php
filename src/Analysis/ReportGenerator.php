<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Analysis;

use Lechimp\Dicto\Definition\Ruleset;
use Lechimp\Dicto\Rules\Rule;

/**
 * Defines how the analysis talks to a report generator.
 */
interface ReportGenerator {
    /**
     * The analyzer will give violations on the given rule afterwards.
     *
     * @param   Ruleset     $rule
     * @return  null
     */
    public function start_ruleset(Ruleset $rule);

    /**
     * The analyzer will give violations on the given rule afterwards.
     *
     * @param   Rule        $rule
     * @return  null
     */
    public function start_rule(Rule $rule);

    /**
     * The analyzer reports a violation on the rule previously given.
     *
     * @param   Violation   $violation
     * @return  null
     */
    public function report_violation(Violation $violation);
}
