<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\DB\IndexDBFactory;
use Lechimp\Dicto\DB\IndexDB;
use Lechimp\Dicto\Report\ResultDB;

require_once(__DIR__."/tempdir.php");

class IndexDBFactoryTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->factory = new IndexDBFactory();
    }

    public function test_build_index_db() {
        $db = $this->factory->build_index_db(tempdir()."/some_database.sqlite");
        $this->assertInstanceOf(IndexDB::class, $db);
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

    public function test_index_db_exists_true() {
        $v = $this->factory->index_db_exists(__DIR__."/data/exists.sqlite");
        $this->assertTrue($v);
    }

    public function test_index_db_exists_false() {
        $v = $this->factory->index_db_exists(__DIR__."/data/not_exists.sqlite");
        $this->assertFalse($v);
    }

    public function test_load_index_db_not_exists() {
        try {
            $this->factory->load_index_db(__DIR__."/data/not_exists.sqlite");  
            $this->assertFalse("This should not happen.");
        }
        catch (\RuntimeException $e) {
            $this->assertNotInstanceOf(PHPUnit_Framework_Exception::class, $e);
        }
    }

    public function test_load_index_db_exists() {
        $db = $this->factory->load_index_db(__DIR__."/data/exists.sqlite");
        $this->assertInstanceOf(IndexDB::class, $db);
    }
}
