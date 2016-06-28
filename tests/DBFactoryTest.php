<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\DBFactory;

require_once(__DIR__."/tempdir.php");

class DBFactoryTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->factory = new DBFactory();
    }

    public function test_dont_use_existing_index_db() {
        try {
            $this->factory->build_index_db(__DIR__."/data/exists.sqlite");
            $this->assertFalse("This should not happen.");
        }
        catch (\RuntimeException $e) {
            $this->assertNotInstanceOf(PHPUnit_Framework_Exception::class, $e);
        }
    }
}
