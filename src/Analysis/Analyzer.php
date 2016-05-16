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

/**
 * Performs the actual analysis of a ruleset over a query-object
 * using a specific rules to sql compiler.
 */
class Analyzer {
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
                        ( Ruleset $ruleset
                        , Query $query
                        , ReportGenerator $generator
                        ) {
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
        $this->generator->start_ruleset($this->ruleset);
        foreach ($this->ruleset->rules() as $rule) {
            $this->generator->start_rule($rule);
            $stmt = $rule->compile($this->query);
            while ($row = $stmt->fetch()) {
                $builder = $this->query->builder();
                $expr = $builder->expr();
                $stmt = $builder
                    ->select("source")
                    ->from($this->query->entity_table())
                    ->where
                        ( $expr->eq("name", $row["file"])
                        , $expr->eq("type", Variable::FILE_TYPE)
                        )
                    ->execute();
                $file = $stmt->fetch();
                if (!is_array($file)) {
                    throw new \RuntimeException(
                        "Could not find ".$row["file"]." in database.");
                }
                $this->generator->report_violation($rule->to_violation($row, $file));
            }
        }
    }
} 
