<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Report\ResultDB;
use Lechimp\Dicto\Report\Queries;
use Lechimp\Dicto\Rules as Rules;
use Lechimp\Dicto\Variables as Vars;
use Lechimp\Dicto\Analysis\Violation;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

abstract class ReportTestBase extends \PHPUnit\Framework\TestCase {
    public function setUp() : void {
        $this->connection = DriverManager::getConnection
            ( array
                ( "driver" => "pdo_sqlite"
                //, "path" => "/home/lechimp/dt.sqlite"
                , "memory" => true
                )
            );
        $this->db = new ResultDB($this->connection);
        $this->db->init_database_schema();
        $this->queries = new Queries($this->db);
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

    protected function init_scenario() {
        // Some scenario containing two rules, three runs
        // and different violations.
        $rule1 = $this->all_classes_cannot_depend_on_globals();
        $rule2 = $this->all_classes_cannot_invoke_functions();
        $vars = array_merge($rule1->variables(), $rule2->variables());
        $ruleset = new Rules\Ruleset($vars, [$rule1, $rule2]);

        $commit1 = "#COMMIT_1#";
        // One violation of rule1, no violation of rule2
        $this->db->begin_run($commit1);
        $this->db->begin_ruleset($ruleset);
        $this->db->begin_rule($rule1);
        $this->db->report_violation(
            new Violation($rule1, "file.php", 42, "file.php_line_42"));
        $this->db->end_rule();
        $this->db->end_ruleset();
        $this->db->end_run();

        $commit2 = "#COMMIT_2#";
        // One new violation of rule1, previous violation still exists.
        // One new violation of rule2.
        $this->db->begin_run($commit2);
        $this->db->begin_ruleset($ruleset);
        $this->db->begin_rule($rule1);
        $this->db->report_violation(
            new Violation($rule1, "file.php", 42, "file.php_line_42"));
        $this->db->report_violation(
            new Violation($rule1, "file2.php", 23, "file2.php_line_23"));
        $this->db->end_rule();
        $this->db->begin_rule($rule2);
        $this->db->report_violation(
            new Violation($rule2, "file3.php", 13, "file2.php_line_13"));
        $this->db->end_rule();
        $this->db->end_ruleset();
        $this->db->end_run();

        $commit3 = "#COMMIT_3#";
        // All violations of rule1 resolved.
        // Two new violation of rule2, previous violation resolved.
        $this->db->begin_run($commit3);
        $this->db->begin_ruleset($ruleset);
        $this->db->begin_rule($rule1);
        $this->db->end_rule();
        $this->db->begin_rule($rule2);
        $this->db->report_violation(
            new Violation($rule2, "file.php", 42, "file.php_line_42"));
        $this->db->report_violation(
            new Violation($rule2, "file2.php", 23, "file2.php_line_23"));
        $this->db->end_rule();
        $this->db->end_ruleset();
        $this->db->end_run();

        $this->rule1 = $rule1;
        $this->rule2 = $rule2;
    }

    // Helper to query ids of known rules.
    public function query_rule_ids() {
        $run = $this->queries->last_run_for("#COMMIT_3#");
        $rules = $this->queries->analyzed_rules($run);
        $info = $this->queries->rule_info($rules[0]);

        if ($info["rule"] == $this->rule1->pprint()) {
            return [$rules[0], $rules[1]];
        }
        else {
            return [$rules[1], $rules[0]];
        }
    }
}
