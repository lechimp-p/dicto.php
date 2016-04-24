<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

use Lechimp\Dicto\Analysis\Consts;
use Lechimp\Dicto\App\DB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class DBInserterTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->connection = DriverManager::getConnection
            ( array
                ( "driver" => "pdo_sqlite"
                , "dbname" => ":memory:"
                , "host" => ""
                , "port" => ""
                , "user" => ""
                , "password" => ""
                )
            ); 
        $this->inserter = new DB($this->connection);
        $this->inserter->create_database();
        $this->builder = $this->connection->createQueryBuilder();
    }

    public function test_insert_entity() {
        $id = $this->inserter->entity(Consts::CLASS_ENTITY, "AClass", "AClass.php", 1, 2, "the source");
        $res = $this->builder
            ->select("*")
            ->from($this->inserter->entity_table())
            ->execute()
            ->fetchAll();

        $expected = array
            ( "id" => $id
            , "type" => Consts::CLASS_ENTITY
            , "name" => "AClass"
            , "file" => "AClass.php"
            , "start_line" => "1"
            , "end_line" => "2"
            , "source" => "the source"
            );

        $this->assertEquals(array($expected), $res);
    }
}
