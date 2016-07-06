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
use Lechimp\Dicto\Analysis\Violation;

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
    }

    protected function builder() {
        return $this->connection->createQueryBuilder();
    }

    // Some example rules

    public function all_classes_cannot_depend_on_globals() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Classes("allClasses")
            , new Rules\DependOn()
            , array(new Vars\Globals("allGlobals"))
            );
    }

    public function all_classes_cannot_invoke_functions() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Classes("allClasses")
            , new Rules\Invoke()
            , array(new Vars\Functions("allFunctions"))
            );
    }

    // Actual Tests

    public function test_smoke() {
        $this->assertTrue($this->db->is_inited());
    }

    public function test_begin_new_run() {
        $this->db->begin_new_run("#COMMIT_HASH#");

        $res = $this->builder()
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
        $this->db->begin_new_run("#COMMIT_HASH1#");
        $this->db->begin_new_run("#COMMIT_HASH2#");

        $res = $this->builder()
            ->select("*")
            ->from($this->db->run_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "commit_hash" => "#COMMIT_HASH1#"
                )
            , array
                ( "id" => "2"
                , "commit_hash" => "#COMMIT_HASH2#"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_begin_rule_inserts_rule() {
        $this->db->begin_new_run("#COMMIT_HASH#");
        $this->db->begin_rule($this->all_classes_cannot_depend_on_globals());

        $res = $this->builder()
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

        $res = $this->builder()
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

    public function test_report_violation() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $violation = new Violation($rule, "file.php", 42, "line of code");
        $this->db->begin_new_run("#COMMIT_HASH#");
        $this->db->begin_rule($rule);
        $this->db->report_violation($violation);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_table())
            ->execute()
            ->fetchAll();
        $expected = array(array
            ( "id" => "1"
            , "rule_id" => "1"
            , "file" => "file.php"
            , "line" => "line of code"
            , "first_seen" => "1"
            , "last_seen" => "1"
            ));
        $this->assertEquals($expected, $res);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_location_table())
            ->execute()
            ->fetchAll();
        $expected = array(array
            ( "id" => "1"
            , "violation_id" => "1"
            , "run_id" => "1"
            , "line_no" => "42"
            ));
        $this->assertEquals($expected, $res);
    }

    public function test_report_violation_twice() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $violation = new Violation($rule, "file.php", 42, "line of code");
        $this->db->begin_new_run("#COMMIT_HASH1#");
        $this->db->begin_rule($rule);
        $this->db->report_violation($violation);
        $this->db->begin_new_run("#COMMIT_HASH2#");
        $this->db->begin_rule($rule);
        $this->db->report_violation($violation);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_table())
            ->execute()
            ->fetchAll();
        $expected = array(array
            ( "id" => "1"
            , "rule_id" => "1"
            , "file" => "file.php"
            , "line" => "line of code"
            , "first_seen" => "1"
            , "last_seen" => "2"
            ));
        $this->assertEquals($expected, $res);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_location_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "violation_id" => "1"
                , "run_id" => "1"
                , "line_no" => "42"
                )
            , array
                ( "id" => "2"
                , "violation_id" => "1"
                , "run_id" => "2"
                , "line_no" => "42"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_report_violation_twice_on_different_line() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $violation1 = new Violation($rule, "file.php", 42, "line of code");
        $violation2 = new Violation($rule, "file.php", 23, "line of code");
        $this->db->begin_new_run("#COMMIT_HASH1#");
        $this->db->begin_rule($rule);
        $this->db->report_violation($violation1);
        $this->db->begin_new_run("#COMMIT_HASH2#");
        $this->db->begin_rule($rule);
        $this->db->report_violation($violation2);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_table())
            ->execute()
            ->fetchAll();
        $expected = array(array
            ( "id" => "1"
            , "rule_id" => "1"
            , "file" => "file.php"
            , "line" => "line of code"
            , "first_seen" => "1"
            , "last_seen" => "2"
            ));
        $this->assertEquals($expected, $res);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_location_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "violation_id" => "1"
                , "run_id" => "1"
                , "line_no" => "42"
                )
            , array
                ( "id" => "2"
                , "violation_id" => "1"
                , "run_id" => "2"
                , "line_no" => "23"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_report_violations_in_different_files() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $violation1 = new Violation($rule, "file1.php", 42, "line of code");
        $violation2 = new Violation($rule, "file2.php", 42, "line of code");
        $this->db->begin_new_run("#COMMIT_HASH1#");
        $this->db->begin_rule($rule);
        $this->db->report_violation($violation1);
        $this->db->begin_new_run("#COMMIT_HASH2#");
        $this->db->begin_rule($rule);
        $this->db->report_violation($violation2);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "rule_id" => "1"
                , "file" => "file1.php"
                , "line" => "line of code"
                , "first_seen" => "1"
                , "last_seen" => "1"
                )
            , array
                ( "id" => "2"
                , "rule_id" => "1"
                , "file" => "file2.php"
                , "line" => "line of code"
                , "first_seen" => "2"
                , "last_seen" => "2"
                )
            );
        $this->assertEquals($expected, $res);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_location_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "violation_id" => "1"
                , "run_id" => "1"
                , "line_no" => "42"
                )
            , array
                ( "id" => "2"
                , "violation_id" => "2"
                , "run_id" => "2"
                , "line_no" => "42"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_report_violations_in_different_lines() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $violation1 = new Violation($rule, "file.php", 42, "line of code");
        $violation2 = new Violation($rule, "file.php", 42, "another line of code");
        $this->db->begin_new_run("#COMMIT_HASH1#");
        $this->db->begin_rule($rule);
        $this->db->report_violation($violation1);
        $this->db->begin_new_run("#COMMIT_HASH2#");
        $this->db->begin_rule($rule);
        $this->db->report_violation($violation2);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "rule_id" => "1"
                , "file" => "file.php"
                , "line" => "line of code"
                , "first_seen" => "1"
                , "last_seen" => "1"
                )
            , array
                ( "id" => "2"
                , "rule_id" => "1"
                , "file" => "file.php"
                , "line" => "another line of code"
                , "first_seen" => "2"
                , "last_seen" => "2"
                )
            );
        $this->assertEquals($expected, $res);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_location_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "violation_id" => "1"
                , "run_id" => "1"
                , "line_no" => "42"
                )
            , array
                ( "id" => "2"
                , "violation_id" => "2"
                , "run_id" => "2"
                , "line_no" => "42"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_report_violations_in_different_rules() {
        $rule1 = $this->all_classes_cannot_depend_on_globals();
        $rule2 = $this->all_classes_cannot_invoke_functions();
        $violation1 = new Violation($rule1, "file.php", 42, "line of code");
        $violation2 = new Violation($rule2, "file.php", 42, "line of code");
        $this->db->begin_new_run("#COMMIT_HASH1#");
        $this->db->begin_rule($rule1);
        $this->db->report_violation($violation1);
        $this->db->begin_rule($rule2);
        $this->db->report_violation($violation2);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "rule_id" => "1"
                , "file" => "file.php"
                , "line" => "line of code"
                , "first_seen" => "1"
                , "last_seen" => "1"
                )
            , array
                ( "id" => "2"
                , "rule_id" => "2"
                , "file" => "file.php"
                , "line" => "line of code"
                , "first_seen" => "1"
                , "last_seen" => "1"
                )
            );
        $this->assertEquals($expected, $res);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_location_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "violation_id" => "1"
                , "run_id" => "1"
                , "line_no" => "42"
                )
            , array
                ( "id" => "2"
                , "violation_id" => "2"
                , "run_id" => "1"
                , "line_no" => "42"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_report_two_violations_in_same_file() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $violation1 = new Violation($rule, "file.php", 23, "line of code");
        $violation2 = new Violation($rule, "file.php", 42, "line of code");
        $this->db->begin_new_run("#COMMIT_HASH1#");
        $this->db->begin_rule($rule);
        $this->db->report_violation($violation1);
        $this->db->report_violation($violation2);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "rule_id" => "1"
                , "file" => "file.php"
                , "line" => "line of code"
                , "first_seen" => "1"
                , "last_seen" => "1"
                )
            );
        $this->assertEquals($expected, $res);

        $res = $this->builder()
            ->select("*")
            ->from($this->db->violation_location_table())
            ->execute()
            ->fetchAll();
        $expected = array
            ( array
                ( "id" => "1"
                , "violation_id" => "1"
                , "run_id" => "1"
                , "line_no" => "23"
                )
            , array
                ( "id" => "2"
                , "violation_id" => "1"
                , "run_id" => "1"
                , "line_no" => "42"
                )
            );
        $this->assertEquals($expected, $res);
    }

}
