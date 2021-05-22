<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Analysis;

use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules\Rule;

/**
 * Listen to events during analysis.
 */
interface Listener
{
    /**
     * Tell the listener that a run begins.
     *
     * @param   string  $commit_hash
     * @return  null
     */
    public function begin_run($commit_hash);

    /**
     * Tell the listener that the run ended.
     *
     * @return null
     */
    public function end_run();

    /**
     * Tell the listener that the analysis of a ruleset began.
     *
     * The analyzer will report on rules in the given ruleset afterwards.
     *
     * TODO: Think about whether this should be reported at all.
     *
     * @param   Ruleset     $ruleset
     * @return  null
     */
    public function begin_ruleset(Ruleset $ruleset);

    /**
     * Tell the listener that the analysis of a ruleset ended.
     *
     * The analyzer will report no more rules in the given ruleset afterwards.
     *
     * @return  null
     */
    public function end_ruleset();

    /**
     * Tell the listener that the analysis for the given rule began.
     *
     * The analyzer will report violations off the given rule afterwards.
     *
     * @param   Rule        $rule
     * @return  null
     */
    public function begin_rule(Rule $rule);

    /**
     * Tell the listener that the analysis for the given rule ended.
     *
     * The analyzer will report no more violations off the given rule afterwards.
     *
     * @return  null
     */
    public function end_rule();

    /**
     * Tell the listener about a violation off the rule reported previously.
     *
     * @param   Violation   $violation
     * @return  null
     */
    public function report_violation(Violation $violation);
}
