<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\App\IndexDB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class IndexDBTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->connection = DriverManager::getConnection
            ( array
                ( "driver" => "pdo_sqlite"
                , "memory" => true
                )
            ); 
        $this->inserter = new IndexDB($this->connection);
        $this->inserter->init_database_schema();
        $this->builder = $this->connection->createQueryBuilder();
    }

    public function test_insert_source_file() {
        $this->inserter->source_file("foo.php", "FOO\nBAR");
        $res = $this->builder
            ->select("*")
            ->from($this->inserter->source_file_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "name" => "foo.php"
                , "line" => "1"
                , "source" => "FOO"
                )
            , array
                ( "name" => "foo.php"
                , "line" => "2"
                , "source" => "BAR"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_insert_entity() {
        $id = $this->inserter->entity(Variable::CLASS_TYPE, "AClass", "AClass.php", 1, 2, "the source");
        $res = $this->builder
            ->select("*")
            ->from($this->inserter->entity_table())
            ->execute()
            ->fetchAll();

        $expected = array
            ( "id" => $id
            , "type" => Variable::CLASS_TYPE
            , "name" => "AClass"
            , "file" => "AClass.php"
            , "start_line" => "1"
            , "end_line" => "2"
            );

        $this->assertEquals(array($expected), $res);
    }

    public function test_insert_reference() {
        $id = $this->inserter->reference(Variable::CLASS_TYPE, "AClass", "AClass.php", 1);
        $res = $this->builder
            ->select("*")
            ->from($this->inserter->reference_table())
            ->execute()
            ->fetchAll();

        $expected = array
            ( "id" => $id
            , "type" => Variable::CLASS_TYPE
            , "name" => "AClass"
            , "file" => "AClass.php"
            , "line" => "1"
            );

        $this->assertEquals(array($expected), $res);
    }

    public function test_insert_dependency() {
        $id1 = $this->inserter->entity(Variable::CLASS_TYPE, "AClass", "AClass.php", 1, 2, "the source");
        $id2 = $this->inserter->reference(Variable::CLASS_TYPE, "AClass", "BClass.php", 1);
        $this->inserter->relation("depend_on", $id1, $id2, "BClass.php", 1, "new AClass();");
        $res = $this->builder
            ->select("*")
            ->from($this->inserter->relations_table())
            ->execute()
            ->fetchAll();

        $expected = array
            ( "entity_id" => "$id1"
            , "reference_id" => "$id2"
            , "name" => "depend_on"
            );

        $this->assertEquals(array($expected), $res);
    }

    public function test_insert_invocation() {
        $id1 = $this->inserter->entity(Variable::CLASS_TYPE, "AClass", "AClass.php", 1, 2, "the source");
        $id2 = $this->inserter->reference(Variable::FUNCTION_TYPE, "my_fun", "AClass.php", 2);
        $this->inserter->relation("invoke", $id1, $id2, "AClass.php", 2, "my_fun();");
        $res = $this->builder
            ->select("*")
            ->from($this->inserter->relations_table())
            ->execute()
            ->fetchAll();

        $expected = array
            ( "entity_id" => "$id1"
            , "reference_id" => "$id2"
            , "name" => "invoke"
            );

        $this->assertEquals(array($expected), $res);
    }

    public function test_id_is_int() {
        $id1 = $this->inserter->entity(Variable::CLASS_TYPE, "AClass", "AClass.php", 1, 2, "the source");
        $this->assertInternalType("integer", $id1);

        $id2 = $this->inserter->reference(Variable::FUNCTION_TYPE, "my_fun", "AClass.php", 2);
        $this->assertInternalType("int", $id2);
    }
}
