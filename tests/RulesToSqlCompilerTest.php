<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto as Dicto;
use Lechimp\Dicto\Analysis\RulesToSqlCompiler;
use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Rules as Rules;
use Lechimp\Dicto\Variables as Vars;
use Lechimp\Dicto\App\IndexDB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class RulesToSqlCompilerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->connection = DriverManager::getConnection
            ( array
                ( "driver" => "pdo_sqlite"
                , "memory" => true
                )
            ); 
        $this->db = new IndexDB($this->connection);
        $this->db->init_sqlite_regexp();
        $this->db->maybe_init_database_schema();
    }


    // All classes cannot contain text "foo".

    public function all_classes_cannot_contain_text_foo() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Classes("allClasses")
            , new Rules\ContainText()
            , array("foo")
            );
    }

    public function test_all_classes_cannot_contain_text_foo_1() {
        $rule = $this->all_classes_cannot_contain_text_foo();
        $this->db->source("file", "foo");
        list($id,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name"        => "$id"
                , "file"        => "file"
                , "source"      => "foo"
                , "line"        => 1
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_all_classes_cannot_contain_text_foo_2() {
        $rule = $this->all_classes_cannot_contain_text_foo();
        $this->db->source("file", "bar");
        list($id,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_all_classes_cannot_contain_text_foo_3() {
        $rule = $this->all_classes_cannot_contain_text_foo();
        $this->db->source("file", "foo");
        list($id,$_) = $this->db->definition("AClass", Variable::FUNCTION_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
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

    public function test_all_classes_cannot_depend_on_globals_1() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE, "file", 2);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_all_classes_cannot_depend_on_globals_2() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $this->db->source("file", "bar\na line");
        list($id,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE, "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_all_classes_cannot_depend_on_globals_3() {
        $rule = $this->all_classes_cannot_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("a_function", Variable::FUNCTION_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE, "file", 2);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }


    // All classes cannot invoke functions.

    public function all_classes_cannot_invoke_functions() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Classes("allClasses")
            , new Rules\Invoke()
            , array(new Vars\Functions("allFunctions"))
            );
    }

    public function test_all_classes_cannot_invoke_functions_1() {
        $rule = $this->all_classes_cannot_invoke_functions();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("a_function", Variable::FUNCTION_TYPE, "file", 2);
        $this->db->relation($id1, $id2, "invoke", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_all_classes_cannot_invoke_functions_2() {
        $rule = $this->all_classes_cannot_invoke_functions();
        $this->db->source("file", "bar\na line");
        list($id,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("a_function", Variable::FUNCTION_TYPE, "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_all_classes_cannot_invoke_functions_3() {
        $rule = $this->all_classes_cannot_invoke_functions();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("some_function", Variable::FUNCTION_TYPE, "file", 1, 2);
        $id2 = $this->db->name("a_function", Variable::FUNCTION_TYPE, "file", 2);
        $this->db->relation($id1, $id2, "invoke", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);
        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }


    // Everything cannot depend on error suppressor.

    public function everything_cannot_depend_on_error_suppressor() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Everything("everything")
            , new Rules\DependOn()
            , array(new Vars\LanguageConstruct("errorSuppressor", "@"))
            );
    }

    /**
     * @dataProvider entity_types_provider
     */
    public function test_everything_cannot_depend_on_error_suppressor_1($type) {
        $rule = $this->everything_cannot_depend_on_error_suppressor();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("entity", $type, "file", 1, 2);
        $id2 = $this->db->name("@", Variable::LANGUAGE_CONSTRUCT_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function entity_types_provider() {
        return array
            ( array(Variable::CLASS_TYPE)
            , array(Variable::FILE_TYPE)
            , array(Variable::FUNCTION_TYPE)
            , array(Variable::METHOD_TYPE)
            );
    }

    public function test_everything_cannot_depend_on_error_suppressor_2() {
        $rule = $this->everything_cannot_depend_on_error_suppressor();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("unset", Variable::LANGUAGE_CONSTRUCT_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }


    // AClasses cannot depend on globals.

    public function a_classes_cannot_depend_on_globals() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\WithProperty
                ( new Vars\Classes("AClasses")
                , new Vars\Name()
                , array("AClass")
                )
            , new Rules\DependOn()
            , array(new Vars\Globals("allGlobals"))
            );
    }

    public function test_a_classes_cannot_depend_on_globals_1() {
        $rule = $this->a_classes_cannot_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_a_classes_cannot_depend_on_globals_2() {
        $rule = $this->a_classes_cannot_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("BClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }


    // All classes cannot depend on globals with name "glob".

    public function all_classes_cannot_depend_on_glob() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Classes("allClasses")
            , new Rules\DependOn()
            , array(new Vars\WithProperty
                ( new Vars\Globals("glob")
                , new Vars\Name()
                , array("glob")
                ))
            );
    }

    public function test_all_classes_cannot_depend_on_glob_1() {
        $rule = $this->all_classes_cannot_depend_on_glob();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_all_classes_cannot_depend_on_glob_2() {
        $rule = $this->all_classes_cannot_depend_on_glob();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("another_glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }


    // Everything but a classes cannot deppend on error suppressor.

    public function everything_but_a_classes_cannot_depend_on_error_suppressor() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Except
                ( new Vars\Everything("everything")
                , new Vars\WithProperty
                    ( new Vars\Classes("AClasses")
                    , new Vars\Name()
                    , array("AClass")
                    )
                )
            , new Rules\DependOn
            , array(new Vars\LanguageConstruct("errorSuppressor", "@"))
            );
    }

    public function test_but_not_1() {
        $rule = $this->everything_but_a_classes_cannot_depend_on_error_suppressor();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("SomeClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("@", Variable::LANGUAGE_CONSTRUCT_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_but_not_2() {
        $rule = $this->everything_but_a_classes_cannot_depend_on_error_suppressor();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("@", Variable::LANGUAGE_CONSTRUCT_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }


    // All classes as well as all functions cannot depend on globals.

    public function all_classes_as_well_as_all_functions_cannot_depend_on_globals() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Any(array
                ( new Vars\Classes("allClasses")
                , new Vars\Functions("allFunctions")
                ))
            , new Rules\DependOn()
            , array(new Vars\Globals("allGlobals"))
            );
    }

    public function test_as_well_as_1() {
        $rule = $this->all_classes_as_well_as_all_functions_cannot_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_as_well_as_2() {
        $rule = $this->all_classes_as_well_as_all_functions_cannot_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("a_function", Variable::FUNCTION_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_as_well_as_3() {
        $rule = $this->all_classes_as_well_as_all_functions_cannot_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("a_method", Variable::METHOD_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }


    // Everything but AClasses must depend on globals.

    public function everything_but_a_classes_must_depend_on_globals() {
        return new Rules\Rule
            ( Rules\Rule::MODE_MUST
            , new Vars\Except
                ( new Vars\Everything("everything")
                , new Vars\WithProperty
                    ( new Vars\Classes("AClasses")
                    , new Vars\Name()
                    , array("AClass")
                    )
                )
            , new Rules\DependOn()
            , array(new Vars\Globals("allGlobals"))
            );
    }

    public function test_must_depend_on_1() {
        $rule = $this->everything_but_a_classes_must_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("a_method", Variable::METHOD_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name"        => "$id1"
                , "file"        => "file"
                , "line"        => "1"
                , "source"      => "foo"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_must_depend_on_2() {
        $rule = $this->everything_but_a_classes_must_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::FUNCTION_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name"        => "$id1"
                , "file"        => "file"
                , "line"        => "1"
                , "source"      => "foo"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_must_depend_on_3() {
        $rule = $this->everything_but_a_classes_must_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::FUNCTION_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_must_depend_on_4() {
        $rule = $this->everything_but_a_classes_must_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE, "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_must_depend_on_5() {
        $rule = $this->everything_but_a_classes_must_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("BClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name"        => "$id1"
                , "file"        => "file"
                , "line"        => "1"
                , "source"      => "foo"
                )
            );
        $this->assertEquals($expected, $res);
    }
    // Only AClasses can depend on globals.

    public function only_a_classes_can_depend_on_globals() {
        return new Rules\Rule
            ( Rules\Rule::MODE_ONLY_CAN
            , new Vars\WithProperty
                ( new Vars\Classes("AClasses")
                , new Vars\Name()
                , array("AClass")
                )
            , new Rules\DependOn()
            , array(new Vars\Globals("allGlobals"))
            );
    }

    public function test_only_can_depend_on_1() {
        $rule = $this->only_a_classes_can_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("a_method", Variable::METHOD_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_only_can_depend_on_2() {
        $rule = $this->only_a_classes_can_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("a_method", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE, "file", 2);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_only_can_depend_on_3() {
        $rule = $this->only_a_classes_can_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::METHOD_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_only_can_depend_on_4() {
        $rule = $this->only_a_classes_can_depend_on_globals();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("glob", Variable::GLOBAL_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    // AClasses must contain text "foo".

    public function a_classes_must_contain_text_foo() {
        return new Rules\Rule
            ( Rules\Rule::MODE_MUST
            , new Vars\WithProperty
                ( new Vars\Classes("AClasses")
                , new Vars\Name()
                , array("AClass")
                )
            , new Rules\ContainText()
            , array("foo")
            );
    }

    public function test_a_classes_must_contain_text_foo_1() {
        $rule = $this->a_classes_must_contain_text_foo();
        $this->db->source("file", "bar\na line");
        list($id,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name"        => "$id"
                , "file"        => "file"
                , "source"      => "bar"
                , "line"        => 1
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_a_classes_must_contain_text_foo_2() {
        $rule = $this->a_classes_must_contain_text_foo();
        $this->db->source("file", "foo\na line");
        list($id,$_) = $this->db->definition("BClass", Variable::CLASS_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_a_classes_must_contain_text_foo_3() {
        $rule = $this->a_classes_must_contain_text_foo();
        $this->db->source("file", "bar\na line");
        list($id,$_) = $this->db->definition("BClass", Variable::CLASS_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_a_classes_must_contain_text_foo_4() {
        $rule = $this->a_classes_must_contain_text_foo();
        $this->db->source("file", "bar\na line");
        list($id,$_) = $this->db->definition("AClass", Variable::FUNCTION_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    // Only AClasses can contain text "foo".

    public function only_a_classes_can_contain_text_foo() {
        return new Rules\Rule
            ( Rules\Rule::MODE_ONLY_CAN
            , new Vars\WithProperty
                ( new Vars\Classes("AClasses")
                , new Vars\Name()
                , array("AClass")
                )
            , new Rules\ContainText()
            , array("foo")
            );
    }

    public function test_only_a_classes_can_contain_text_foo_1() {
        $rule = $this->only_a_classes_can_contain_text_foo();
        $this->db->source("file", "foo\na line");
        list($id,$_) = $this->db->definition("BClass", Variable::CLASS_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name"        => "$id"
                , "file"        => "file"
                , "source"      => "foo"
                , "line"        => 1
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_only_a_classes_can_contain_text_foo_2() {
        $rule = $this->only_a_classes_can_contain_text_foo();
        $this->db->source("file", "foo\na line");
        list($id,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_only_a_classes_can_contain_text_foo_3() {
        $rule = $this->only_a_classes_can_contain_text_foo();
        $this->db->source("file", "a line\nfoo");
        list($id,$_) = $this->db->definition("AClass", Variable::FUNCTION_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name"        => "$id"
                , "file"        => "file"
                , "source"      => "foo"
                , "line"        => 2
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_only_a_classes_can_contain_text_foo_4() {
        $rule = $this->only_a_classes_can_contain_text_foo();
        $this->db->source("file", "bar\na line");
        list($id,$_) = $this->db->definition("BClass", Variable::CLASS_TYPE, "file", 1, 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    // AClasses must invoke functions.

    public function a_classes_must_invoke_functions() {
        return new Rules\Rule
            ( Rules\Rule::MODE_MUST
            , new Vars\WithProperty
                ( new Vars\Classes("AClasses")
                , new Vars\Name()
                , array("AClass")
                )
            , new Rules\Invoke()
            , array(new Vars\Functions("allFunctions"))
            );
    }

    public function test_a_classes_must_invoke_functions_1() {
        $rule = $this->a_classes_must_invoke_functions();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("a_function", Variable::FUNCTION_TYPE);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name"        => "$id1"
                , "file"        => "file"
                , "line"        => "1"
                , "source"      => "foo"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_a_classes_must_invoke_functions_2() {
        $rule = $this->a_classes_must_invoke_functions();
        $this->db->source("file", "bar\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("a_function", Variable::FUNCTION_TYPE);
        $this->db->relation($id1, $id2, "invoke", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_a_classes_must_invoke_functions_3() {
        $rule = $this->a_classes_must_invoke_functions();
        $this->db->source("file", "bar\na line");
        list($id1,$_) = $this->db->definition("BClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("a_function", Variable::FUNCTION_TYPE);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }


    // Only AClasses can invoke functions.

    public function only_a_classes_can_invoke_functions() {
        return new Rules\Rule
            ( Rules\Rule::MODE_ONLY_CAN
            , new Vars\WithProperty
                ( new Vars\Classes("AClasses")
                , new Vars\Name()
                , array("AClass")
                )
            , new Rules\Invoke()
            , array(new Vars\Functions("allFunctions"))
            );
    }

    public function test_only_a_classes_can_invoke_function_1() {
        $rule = $this->only_a_classes_can_invoke_functions();
        $this->db->source("file", "foo\na line");
        list($id1,$_) = $this->db->definition("BClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("a_function", Variable::FUNCTION_TYPE);
        $this->db->relation($id1, $id2, "invoke", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }

    public function test_only_a_classes_can_invoke_function_2() {
        $rule = $this->only_a_classes_can_invoke_functions();
        $this->db->source("file", "bar\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 2);
        $id2 = $this->db->name("a_function", Variable::FUNCTION_TYPE);
        $this->db->relation($id1, $id2, "invoke", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $this->assertEquals(array(), $res);
    }

    public function test_only_a_classes_can_invoke_function_3() {
        $rule = $this->only_a_classes_can_invoke_functions();
        $this->db->source("file", "bar\na line");
        list($id1,$_) = $this->db->definition("AClass", Variable::FUNCTION_TYPE, "file", 1, 2);
        $id2 = $this->db->name("a_function", Variable::FUNCTION_TYPE);
        $this->db->relation($id1, $id2, "invoke", "file", 2);
        $stmt = $rule->compile($this->db);

        $this->assertInstanceOf("\\Doctrine\\DBAL\\Driver\\Statement", $stmt);

        $res = $stmt->fetchAll();
        $expected = array
            ( array
                ( "name_left"       => "$id1"
                , "name_right"    => "$id2"
                , "file"            => "file"
                , "line"            => 2
                , "source"          => "a line"
                )
            );
        $this->assertEquals($expected, $res);
    }
}
