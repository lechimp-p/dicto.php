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

use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Variables\Variable;

/**
 * Performs the actual analysis of a ruleset over a query-object
 * using a specific rules to sql compiler.
 */
class Analyzer {
    /**
     * @var Def\Ruleset
     */
    protected $ruleset;

    /**
     * @var Query
     */
    protected $query;

    public function __construct
                        ( Def\Ruleset $ruleset
                        , Query $query
                        ) {
        $this->ruleset = $ruleset;
        $this->query = $query;
    }

    /**
     * Run the analysis.
     *
     * @param   \Closure    $process_violation  Expected to take violations and do whatever
     * @return  null
     */
    public function run(\Closure $process_violation) {
        foreach ($this->ruleset->rules() as $rule) {
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
                $process_violation($rule->to_violation($row, $file));
            }
        }
    }
} 
