<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
 */

use Lechimp\Dicto as Dicto;
use Lechimp\Dicto\Definition as Def;

require_once(__DIR__."/AnalyzerTest.php");

class PHPDependAnalyzerTest extends AnalyzerTest {
    protected function get_analyzer(Def\Ruleset $ruleset) {
        return Dicto\Analysis\PHPDepend\Analyzer::instantiate_for($ruleset);
    }

}
