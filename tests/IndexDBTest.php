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
        $this->db = new IndexDB($this->connection);
        $this->db->init_database_schema();
    }

    public function builder() {
        return $this->connection->createQueryBuilder();
    }

    public function test_insert_source_file() {
        $this->db->source_file("foo.php", "FOO\nBAR");
        $builder = $this->builder();
        $b = $builder->expr();
        $res = $builder
            ->select("f.path", "s.line", "s.source")
            ->from($this->db->file_table(), "f")
            ->join
                ( "f", $this->db->source_table(), "s"
                , $b->eq("f.id", "s.file")
                )
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "path" => "foo.php"
                , "line" => "1"
                , "source" => "FOO"
                )
            , array
                ( "path" => "foo.php"
                , "line" => "2"
                , "source" => "BAR"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_insert_entity() {
        $this->db->source_file("AClass.php", "FOO\nBAR");
        $id = $this->db->entity(Variable::CLASS_TYPE, "AClass", "AClass.php", 1, 2);

        $builder = $this->builder();
        $b = $builder->expr();
        $res = $builder
            ->select
                ( "n.id"
                , "n.name"
                , "n.type"
                , "f.path"
                , "d.start_line"
                , "d.end_line"
                )
            ->from($this->db->definition_table(), "d")
            ->join
                ( "d", $this->db->name_table(), "n"
                , $b->eq("d.name", "n.id")
                )
            ->join
                ( "d", $this->db->file_table(), "f"
                , $b->eq("d.file", "f.id")
                )
            ->execute()
            ->fetchAll();
        $expected = array
            ( "id" => "$id"
            , "name" => "AClass"
            , "type" => Variable::CLASS_TYPE
            , "path" => "AClass.php"
            , "start_line" => "1"
            , "end_line" => "2"
            );
        $this->assertEquals(array($expected), $res);
    }

    public function test_insert_reference() {
        $this->db->source_file("AClass.php", "FOO\nBAR");
        $id = $this->db->reference(Variable::CLASS_TYPE, "AClass", "AClass.php", 1);

        $builder = $this->builder();
        $b = $builder->expr();
        $res = $builder
            ->select
                ( "n.id"
                , "n.name"
                , "n.type"
                , "f.path"
                , "r.line"
                )
            ->from($this->db->name_table(), "n")
            ->join
                ( "n", $this->db->reference_table(), "r"
                , $b->eq("n.id","r.name")
                )
            ->join
                ( "r", $this->db->file_table(), "f"
                , $b->eq("r.file", "f.id")
                )
            ->execute()
            ->fetchAll();
        $expected = array
            ( "id" => $id
            , "name" => "AClass"
            , "type" => Variable::CLASS_TYPE
            , "path" => "AClass.php"
            , "line" => "1"
            );
        $this->assertEquals(array($expected), $res);
    }

    public function test_insert_some_relation() {
        $this->db->source_file("AClass.php", "FOO\nBAR");
        $this->db->source_file("BClass.php", "FOO\nBAR");
        $id1 = $this->db->entity(Variable::CLASS_TYPE, "AClass", "AClass.php", 1, 2, "the source");
        $id2 = $this->db->reference(Variable::CLASS_TYPE, "AClass", "BClass.php", 1);
        $this->db->relation("some_relation", $id1, $id2, "BClass.php", 1);

        $builder = $this->builder();
        $b = $builder->expr();
        $res = $builder
            ->select
                ( "r.name_left"
                , "r.name_right"
                , "r.which"
                , "f.path"
                , "r.line"
                )
            ->from($this->db->relation_table(), "r")
            ->join
                ( "r", $this->db->file_table(), "f"
                , $b->eq("r.file", "f.id")
                )
            ->execute()
            ->fetchAll();
        $expected = array
            ( "name_left" => "$id1"
            , "name_right" => "$id2"
            , "which" => "some_relation"
            , "path" => "BClass.php"
            , "line" => "1"
            );

        $this->assertEquals(array($expected), $res);
    }

    public function test_id_is_int() {
        $this->db->source_file("AClass.php", "FOO\nBAR");
        $id1 = $this->db->entity(Variable::CLASS_TYPE, "AClass", "AClass.php", 1, 2, "the source");
        $this->assertInternalType("integer", $id1);

        $id2 = $this->db->reference(Variable::FUNCTION_TYPE, "my_fun", "AClass.php", 2);
        $this->assertInternalType("int", $id2);
    }
}
