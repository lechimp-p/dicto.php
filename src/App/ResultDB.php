<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Analysis\ReportGenerator;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules\Rule;

class ResultDB implements ReportGenerator {
    // ReportGenerator implementation

    /**
     * @inheritdoc
     */
    public function begin_ruleset(Ruleset $rule) {
    }

    /**
     * @inheritdoc
     */
    public function end_ruleset(Ruleset $rule) {
    }

    /**
     * @inheritdoc
     */
    public function begin_rule(Rule $rule) {
    }

    /**
     * @inheritdoc
     */
    public function end_rule(Rule $rule) {
    }

    /**
     * @inheritdoc
     */
    public function report_violation(Violation $violation) {
    }

    // Names

    public function run_table() {
        return "runs";    
    }

    public function rule_table() {
        return "rules";
    }

    public function violation_table() {
        return "violations";
    }

    // Creation of database.

    public function maybe_init_database_schema() {
        $res = $this->builder()
            ->select("COUNT(*)")
            ->from("sqlite_master")
            ->where("type = 'table'")
            ->execute()
            ->fetchColumn();
        if ($res == 0) {
            $this->init_database_schema();
        }
    }

    public function init_database_schema() {
        $schema = new Schema\Schema();

        $run_table = $schema->createTable($this->run_table());
        $run_table->addColumn
            ("id", "integer"
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $run_table->addColumn
            ( "commit", "string"
            , array("notnull" => true)
            );
        // TODO: maybe add time
        // TODO: do we need some other meta information per run of the analysis? 
        $run_table->setPrimaryKey(array("id"));

        // TODO: looks like i need to add the variable definitions as well

        $rule_table = $schema->createTable($this->rule_table());
        $rule_table->addColumn
            ( "rule", "string"
            , array("notnull" => true)
            );
        $rule_table->addColumn
            ( "first_seen", "int"
            , array("notnull" => true)
            );
        $rule_table->addColumn
            ( "last_seen", "int"
            , array("notnull" => false)
            );
        $rule_table->addForeignKeyConstraint
            ( $run_table
            , array("first_seen")
            , array("id")
            );
        $rule_table->addForeignKeyConstraint
            ( $run_table
            , array("last_seen")
            , array("id")
            );

        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }

}
