<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Definition\Symbol;

class SymbolText extends PHPUnit_Framework_TestCase {
    public function test_valid_regexp_only() {
        try {
            new Symbol("(a", 10);
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
}
