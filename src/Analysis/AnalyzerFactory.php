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
use Psr\Log\LoggerInterface as Log;

/**
 * Creates analyzers.
 */
class AnalyzerFactory {
    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Ruleset
     */
    protected $ruleset;

    public function __construct
                        ( Log $log
                        , RuleSet $ruleset
                        ) {
        $this->log = $log;
        $this->ruleset = $ruleset;
    }

    /**
     * @param   Query               $query
     * @param   ReportGenerator     $report_generator
     * @return  Analyzer
     */
    public function build(Query $query, ReportGenerator $report_generator) {
        return new Analyzer($this->log, $this->ruleset, $query, $report_generator);
    }
} 
