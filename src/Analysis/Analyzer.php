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

/**
 * Interface to a facility that analyses a codebase.
 */
interface Analyzer {
    /**
     * Get an instance of the analyzer for a set of rules.
     *
     * @param   Def\Ruleset     $ruleset
     * @return  Analyzer
     */
    public static function instantiate_for(Def\Ruleset $ruleset);

    /**
     * Run the analyzer on a codebase located at $src.
     *
     * @param   string  $src
     * @return  Result
     */
    public function run_analysis_on($src);
} 
