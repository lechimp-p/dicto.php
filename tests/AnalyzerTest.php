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
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Rules;
use Lechimp\Dicto\Rules\Rule;
use Lechimp\Dicto\Definition\Ruleset;
use Lechimp\Dicto\Variables as Vars;
use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\App\DB;
use Lechimp\Dicto\Analysis\RulesToSqlCompiler;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Analysis\ReportGenerator;
use Doctrine\DBAL\DriverManager;

class ReportGeneratorMock implements ReportGenerator {
    public $violations = array();
    public function report_violation(Violation $violation) {
        $this->violations[] = $violation;
    }
    public function start_ruleset(Ruleset $rule) {}
    public function start_rule(Rule $rule) {}
}

class AnalyzerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->connection = DriverManager::getConnection
            ( array
                ( "driver" => "pdo_sqlite"
                , "memory" => true
                )
            );
        $this->db = new DB($this->connection);
        $this->db->init_sqlite_regexp();
        $this->db->maybe_init_database_schema();

        $this->rp = new ReportGeneratorMock();
   }

    public function analyzer(Rules\Rule $rule) {
        $ruleset = new Def\Ruleset($rule->variables(), array($rule));
        return new Dicto\Analysis\Analyzer($ruleset, $this->db, $this->rp);
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
        $analyzer = $this->analyzer($rule);

        $code = <<<CODE
1
2
3
foo
4
5
6
CODE;

        $this->db->entity(Variable::FILE_TYPE, "file", "file", 1, 2, $code);
        $this->db->entity(Variable::CLASS_TYPE, "AClass", "file", 1, 2, $code);

        $analyzer->run();
        $expected = array(new Violation
            ( $rule
            , "file"
            , 4
            , "foo"
            ));
        $this->assertEquals($expected, $this->rp->violations);
    }


    // All functions cannot depend on methods.

    public function all_functions_cannot_depend_on_methods() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Functions("allFunctions")
            , new Rules\DependOn()
            , array(new Vars\Methods("allMethods"))
            );
    }

    public function test_all_functions_cannot_depend_on_methods() {
        $rule = $this->all_functions_cannot_depend_on_methods();
        $analyzer = $this->analyzer($rule);

        $code = <<<CODE
1
2
3
foo
4
5
6
CODE;

        $this->db->entity(Variable::FILE_TYPE, "file", "file", 1, 2, $code);
        $id1 = $this->db->entity(Variable::FUNCTION_TYPE, "AClass", "file", 1, 2, $code);
        $id2 = $this->db->reference(Variable::METHOD_TYPE, "a_method", "file", 4, "foo");
        $this->db->relation("depend_on", $id1, $id2, "file", 4, "foo");

        $analyzer->run();
        $expected = array(new Violation
            ( $rule
            , "file"
            , 4
            , "foo"
            ));
        $this->assertEquals($expected, $this->rp->violations);
    }
}
