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
use Lechimp\Dicto\Rules as Rules;
use Lechimp\Dicto\Variables as Vars;

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

    // All classes cannot depend on globals.

    public function all_classes_cannot_depend_on_globals() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Classes("allClasses")
            , new Rules\DependOn()
            , array(new Vars\Globals("allGlobals"))
            );
    }

    // Actual Tests

    public function test_smoke() {
        $this->assertTrue($this->db->is_inited());
    }

    public function test_begin_new_run() {
        $this->db->begin_new_run("#COMMIT_HASH#");

        $res = $this->builder
            ->select("*")
            ->from($this->db->run_table())
            ->execute()
            ->fetchAll();
        $expected = array(array
            ( "id" => "1"
            , "commit_hash" => "#COMMIT_HASH#"
            ));
        $this->assertEquals($expected, $res);
    }

    public function test_begin_two_new_runs() {
        $this->db->begin_new_run("#COMMIT_HASH#");
        $this->db->begin_new_run("#COMMIT_HASH#");

        $res = $this->builder
            ->select("*")
            ->from($this->db->run_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "commit_hash" => "#COMMIT_HASH#"
                )
            , array
                ( "id" => "2"
                , "commit_hash" => "#COMMIT_HASH#"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_begin_rule_inserts_rule() {
        $this->db->begin_new_run("#COMMIT_HASH#");
        $this->db->begin_rule($this->all_classes_cannot_depend_on_globals());

        $res = $this->builder
            ->select("*")
            ->from($this->db->rule_table())
            ->execute()
            ->fetchAll();
        $expected = array(array
            ( "id" => "1"
            , "rule" => "allClasses cannot depend on allGlobals"
            , "first_seen" => "1"
            , "last_seen" => "1"
            ));
        $this->assertEquals($expected, $res);
    }

    public function test_begin_rule_inserts_rule_twice() {
        $this->db->begin_new_run("#COMMIT_HASH1#");
        $this->db->begin_rule($this->all_classes_cannot_depend_on_globals());
        $this->db->begin_new_run("#COMMIT_HASH2#");
        $this->db->begin_rule($this->all_classes_cannot_depend_on_globals());

        $res = $this->builder
            ->select("*")
            ->from($this->db->rule_table())
            ->execute()
            ->fetchAll();
        $expected = array(array
            ( "id" => "1"
            , "rule" => "allClasses cannot depend on allGlobals"
            , "first_seen" => "1"
            , "last_seen" => "2"
            ));
        $this->assertEquals($expected, $res);
    }
}
