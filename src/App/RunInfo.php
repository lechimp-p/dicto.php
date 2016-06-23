<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\RulesRuleset;

/**
 * Information about one run of the analysis.
 */
class RunInfo {
    /**
     * @var string
     */
    private $commit_hash;

    /**
     * @var RuleSet
     */
    private $rule_set;

    public function __construct($commit_hash, RuleSet $rule_set) {
        assert('is_string($commit_hash)');
        $this->commit_hash = $commit_hash;
        $this->rule_set = $rule_set;
    }

    /**
     * @return  string
     */
    public function commit_hash() {
        return $this->commit_hash;
    }

    /**
     * @return  RuleSet
     */
    public function rule_set() {
        return $this->rule_set;
    }
}
