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

    public function __construct
                        ( Log $log
                        , ReportGenerator $generator
                        ) {
        $this->log = $log;
        $this->generator = $generator;
    }

    /**
     * @param   RuleSet     $ruleset
     * @param   Query       $query
     * @return  Analyzer
     */
    public function build(RuleSet $ruleset, Query $query) {
        return new Analyzer($this->log, $ruleset, $query, $this->generator);
    }
} 
