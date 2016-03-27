<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Analysis;
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Output\RulePrinter;

class Result {
    /**
     * @var Def\Ruleset
     */
    protected $ruleset;

    /**
     * @var Violation[] 
     */
    protected $violations;

    /**
     * @var array
     */
    protected $by_rule_cache;

    /**
     * @var array
     */
    protected $by_filename_cache;

    /**
     * @param   Def\Ruleset     $ruleset
     * @param   Violations[]    $violations
     */
    public function __construct(Def\Ruleset $ruleset, array $violations) {
        $this->ruleset = $ruleset;
        $this->violations = array_map(function(Violation $v) {
            return $v;
        }, $violations);
        $this->by_rule_cache = array();
        $this->by_filename_cache = array();
        $this->pprinter = new RulePrinter;
    }

    /**
     * @return  Def\Ruleset
     */
    public function ruleset() {
        return $this->ruleset;
    }

    /**
     * @param   Def\Rules\Rule  $rule
     * @return  Violation[]
     */
    public function violations_of(Def\Rules\Rule $rule) {
        $r = $this->pprinter->pprint($rule);
        if (array_key_exists($r, $this->by_rule_cache)) {
            return $this->by_rule_cache[$r];
        }

        $vs = array();
        foreach ($this->violations as $v) {
            if ($v->rule() == $rule) {
                $vs[] = $v;
            }
        }

        $this->by_rule_cache[$r] = $vs;
        return $vs;
    }

    /**
     * @param   string          $filename
     * @return  Violation[]
     */
    public function violations_in($filename) {
        assert('is_string($filename)');
        if (array_key_exists($filename, $this->by_filename_cache)) {
            return $this->by_filename_cache[$filename];
        }

        $vs = array();
        foreach ($this->violations as $v) {
            if ($v->filename() == $filename) {
                $vs[] = $v;
            }
        }

        $this->by_filename_cache[$filename] = $vs;
        return $vs;
    }
}
