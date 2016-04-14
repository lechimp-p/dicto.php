<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Analysis\PDepend;

/**
 * Analyzer factory stub to instantiate the \PDepend\Engine.
 */
class AnalyzerFactory extends \PDepend\Metrics\AnalyzerFactory {
    public function __construct() {
    }

    public function createRequiredForGenerators(array $generators) {
        if (count($generators) > 0) {
            throw new \LogicException("I did not expect this to be called for real..."); 
        }
        return array();
    } 
}
