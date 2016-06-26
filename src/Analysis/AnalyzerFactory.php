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

use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Rules\RuleSet;

/**
 * Creates analyzers.
 */
class AnalyzerFactory {
    /**
     * @var Log
     */
    protected $log;

    /**
     * @var ReportGenerator
     */
    protected $generator;

    /**
     * @var Ruleset
     */
    protected $ruleset;

    public function __construct
                        ( Log $log
                        , ReportGenerator $generator
                        , RuleSet $ruleset
                        ) {
        $this->log = $log;
        $this->generator = $generator;
        $this->ruleset = $ruleset;
    }

    /**
     * @param   RuleSet     $ruleset
     * @param   Query       $query
     * @return  Analyzer
     */
    public function build(Query $query) {
        return new Analyzer($this->log, $this->ruleset, $query, $this->generator);
    }
} 
