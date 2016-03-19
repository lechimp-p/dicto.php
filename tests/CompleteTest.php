<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;

class CompleteTest extends PHPUnit_Framework_TestCase {
    protected static $violations;

    public function setUpBeforeClass() {
        $analyzer = $this->get_analyzer(__DIR__."/data/rules.php", __DIR__."/data/content");
        self::$violations = $analyzer->result();
    }

    protected function get_rule($rule) {
    }
}
