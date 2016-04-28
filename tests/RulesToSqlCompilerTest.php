<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

use Lechimp\Dicto as Dicto;
use Lechimp\Dicto\Analysis\RulesToSqlCompiler;
use Lechimp\Dicto\Analysis\Consts;
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Definition\Rules as R;
use Lechimp\Dicto\Definition\Variables as V;
use Lechimp\Dicto\App\DB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class RulesToSqlCompilerTest extends PHPUnit_Framework_TestCase {
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

        // initialize regexp function for sqlite
        $pdo = $this->connection->getWrappedConnection();
        $pdo->sqliteCreateFunction("regexp", function($pattern, $data) {
            return preg_match("%$pattern%", $data) > 0;
        });

        $this->db = new DB($this->connection);
        $this->db->create_database();
        $this->compiler = new RulesToSqlCompiler();
    }

    public function all_classes_cannot_contain_text_foo() {
        return new R\ContainText
            ( R\Rule::MODE_CANNOT
            , new V\Classes("allClasses")
            , "foo"
            );
    }

    public function all_classes_cannot_depend_on_globals() {
        return new R\DependOn
            ( R\Rule::MODE_CANNOT
            , new V\Classes("allClasses")
            , new V\Globals("allGlobals")
            );
    }

    public function all_classes_cannot_invoke_functions() {
        return new R\Invoke
            ( R\Rule::MODE_CANNOT
            , new V\Classes("allClasses")
            , new V\Functions("allFunctions")
            );
    }

    public function test_all_classes_cannot_contain_text_foo_1() {
        $rule = $this->all_classes_cannot_contain_text_foo();
        $id = $this->db->entity(Consts::CLASS_ENTITY, "AClass", "file", 1, 2, "foo");
        $stmt = $this->compiler->compile($this->db, $rule);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "id"          => "$id"
                , "type"        => Consts::CLASS_ENTITY
                , "name"        => "AClass"
                , "file"        => "file"
                , "start_line"  => "1"
                , "end_line"    => "2"
                , "source"      => "foo"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_all_classes_cannot_contain_text_foo_2() {
        $rule = $this->all_classes_cannot_contain_text_foo();
        $id = $this->db->entity(Consts::CLASS_ENTITY, "AClass", "file", 1, 2, "bar");
        $stmt = $this->compiler->compile($this->db, $rule);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_all_classes_cannot_depend_on_globals_1() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $id1 = $this->db->entity(Consts::CLASS_ENTITY, "AClass", "file", 1, 2, "foo");
        $id2 = $this->db->reference(Consts::GLOBAL_ENTITY, "glob", "file", 2);
        $this->db->dependency($id1, $id2, "file", 2, "a line");
        $stmt = $this->compiler->compile($this->db, $rule);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "dependent_id"    => "$id1"
                , "dependency_id"   => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source_line"     => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_all_classes_cannot_depend_on_globals_2() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $id = $this->db->entity(Consts::CLASS_ENTITY, "AClass", "file", 1, 2, "bar");
        $id2 = $this->db->reference(Consts::GLOBAL_ENTITY, "glob", "file", 2);
        $stmt = $this->compiler->compile($this->db, $rule);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_all_classes_cannot_depend_on_globals_3() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $id1 = $this->db->entity(Consts::FUNCTION_ENTITY, "a_function", "file", 1, 2, "foo");
        $id2 = $this->db->reference(Consts::GLOBAL_ENTITY, "glob", "file", 2);
        $this->db->dependency($id1, $id2, "file", 2, "a line");
        $stmt = $this->compiler->compile($this->db, $rule);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_all_classes_cannot_invoke_functions_1() {
        $rule = $this->all_classes_cannot_invoke_functions();
        $id1 = $this->db->entity(Consts::CLASS_ENTITY, "AClass", "file", 1, 2, "foo");
        $id2 = $this->db->reference(Consts::FUNCTION_ENTITY, "a_function", "file", 2);
        $this->db->invocation($id1, $id2, "file", 2, "a line");
        $stmt = $this->compiler->compile($this->db, $rule);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "invoker_id"      => "$id1"
                , "invokee_id"      => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source_line"     => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_all_classes_cannot_invoke_functions_2() {
        $rule = $this->all_classes_cannot_invoke_functions();
        $id = $this->db->entity(Consts::CLASS_ENTITY, "AClass", "file", 1, 2, "bar");
        $id2 = $this->db->reference(Consts::FUNCTION_ENTITY, "a_function", "file", 2);
        $stmt = $this->compiler->compile($this->db, $rule);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_all_classes_cannot_invoke_functions_3() {
        $rule = $this->all_classes_cannot_invoke_functions();
        $id1 = $this->db->entity(Consts::FUNCTION_ENTITY, "some_function", "file", 1, 2, "foo");
        $id2 = $this->db->reference(Consts::FUNCTION_ENTITY, "a_function", "file", 2);
        $this->db->invocation($id1, $id2, "file", 2, "a line");
        $stmt = $this->compiler->compile($this->db, $rule);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);
        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }
}
