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

use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Variables\Variable;
use Psr\Log\LoggerInterface as Log;

/**
 * Performs the actual analysis of a ruleset over a query-object
 * using a specific rules to sql compiler.
 */
class Analyzer {
    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Ruleset
     */
    protected $ruleset;

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var ReportGenerator
     */
    protected $generator;

    public function __construct
                        ( Log $log
                        , Ruleset $ruleset
                        , Query $query
                        , ReportGenerator $generator
                        ) {
        $this->log = $log;
        $this->ruleset = $ruleset;
        $this->query = $query;
        $this->generator = $generator;
    }

    /**
     * Run the analysis.
     *
     * @return  null
     */
    public function run() {
        $this->generator->begin_ruleset($this->ruleset);
        foreach ($this->ruleset->rules() as $rule) {
            $this->log->info("checking: ".$rule->pprint());
            $this->generator->begin_rule($rule);
            $stmt = $rule->compile($this->query);
            while ($row = $stmt->fetch()) {
                $this->generator->report_violation($rule->to_violation($row));
            }
            $this->generator->end_rule($rule);
        }
        $this->generator->end_ruleset($this->ruleset);
    }
} 
