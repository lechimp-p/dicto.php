<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\ResultDB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class ResultDBTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->connection = DriverManager::getConnection
            ( array
                ( "driver" => "pdo_sqlite"
                , "memory" => true
                )
            ); 
        $this->db = new ResultDB($this->connection);
        $this->db ->init_database_schema();
        $this->builder = $this->connection->createQueryBuilder();
    }

    public function test_smoke() {
        
    }
}
