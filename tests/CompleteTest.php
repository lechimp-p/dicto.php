<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;

abstract class CompleteTest extends PHPUnit_Framework_TestCase {
    protected static $violations;

    abstract static protected function get_app();

    static public function setUpBeforeClass() {
        $run = static::get_app(__DIR__."/data/rules.php", __DIR__."/data/content");
        self::$violations = $analyzer->result();
    }

    protected function get_rule($rule) {
    }
}
