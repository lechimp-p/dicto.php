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

require_once(__DIR__."/RuleLoaderTest.php");

class RuleLoaderImplementationTest extends RuleLoaderTest { 
    protected function get_rule_loader() {
        return new Dicto\App\Implementation\RuleLoader();
    }
}
