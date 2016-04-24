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

    public function test_insert_reference() {
        $id = $this->inserter->reference(Consts::CLASS_ENTITY, "AClass", "AClass.php", 1);
        $res = $this->builder
            ->select("*")
            ->from($this->inserter->reference_table())
            ->execute()
            ->fetchAll();

        $expected = array
            ( "id" => $id
            , "type" => Consts::CLASS_ENTITY
            , "name" => "AClass"
            , "file" => "AClass.php"
            , "line" => "1"
            );

        $this->assertEquals(array($expected), $res);
    }

    public function test_insert_dependency() {
        $id1 = $this->inserter->entity(Consts::CLASS_ENTITY, "AClass", "AClass.php", 1, 2, "the source");
        $id2 = $this->inserter->reference(Consts::CLASS_ENTITY, "AClass", "BClass.php", 1);
        $this->inserter->dependency($id1, $id2, "BClass.php", 1, "new AClass();");
        $res = $this->builder
            ->select("*")
            ->from($this->inserter->dependencies_table())
            ->execute()
            ->fetchAll();

        $expected = array
            ( "dependent_id" => $id1
            , "dependency_id" => $id2
            , "file" => "BClass.php"
            , "line" => "1"
            , "source_line" => "new AClass();"
            );

        $this->assertEquals(array($expected), $res);
    }

    public function test_insert_invocation() {
        $id1 = $this->inserter->entity(Consts::CLASS_ENTITY, "AClass", "AClass.php", 1, 2, "the source");
        $id2 = $this->inserter->reference(Consts::FUNCTION_ENTITY, "my_fun", "AClass.php", 2);
        $this->inserter->invocation($id1, $id2, "AClass.php", 2, "my_fun()"); 
        $res = $this->builder
            ->select("*")
            ->from($this->inserter->invocations_table())
            ->execute()
            ->fetchAll();

        $expected = array
            ( "invoker_id" => $id1
            , "invokee_id" => $id2
            , "file" => "AClass.php"
            , "line" => "2"
            , "source_line" => "my_fun();"
            );

        $this->assertEquals(array($expected), $res);
    }
}
