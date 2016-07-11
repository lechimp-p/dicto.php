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
use Lechimp\Dicto\Variables\Variable;
use Doctrine\DBAL\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

class ResultDB extends DB implements ReportGenerator {
    /**
     * @var int|null
     */
    private $current_run_id = null;

    /**
     * @var int|null
     */
    private $current_rule_id = null;

    // ReportGenerator implementation

    /**
     * Announce to start a new run of the analysis now.
     *
     * @param   string  $commit_hash
     * @return  null
     */
    public function begin_new_run($commit_hash) {
        assert('is_string($commit_hash)');
        $this->builder()
            ->insert($this->run_table())
            ->values(array
                ( "commit_hash" => "?"
                ))
            ->setParameter(0, $commit_hash)
            ->execute();
        $this->current_run_id = (int)$this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function begin_ruleset(Ruleset $rule) {
        // Nothing to do here...
    }

    /**
     * @inheritdoc
     */
    public function end_ruleset(Ruleset $rule) {
        // Nothing to do here...
    }

    /**
     * @inheritdoc
     */
    public function begin_rule(Rule $rule) {
        assert('$this->current_run_id !== null');
        $rule_id = $this->rule_id($rule);
        if ($rule_id === null) {
            $this->builder()
                ->insert($this->rule_table())
                ->values(array
                    ( "rule" => "?"
                    , "first_seen" => "?"
                    , "last_seen" => "?"
                    ))
                ->setParameter(0, $rule->pprint())
                ->setParameter(1, $this->current_run_id)
                ->setParameter(2, $this->current_run_id)
                ->execute();
            $rule_id = (int)$this->connection->lastInsertId();
        }
        else {
            $this->builder()
                ->update($this->rule_table())
                ->set("last_seen", "?")
                ->where("id = ?")
                ->setParameter(0, $this->current_run_id)
                ->setParameter(1, $rule_id)
                ->execute();
        }
        foreach ($rule->variables() as $variable) {
            $this->upsert_variable($variable);
        } 
        $this->current_rule_id = $rule_id;
    }

    /**
     * @inheritdoc
     */
    public function end_rule(Rule $rule) {
        $this->current_rule_id = null;
    }

    /**
     * @inheritdoc
     */
    public function report_violation(Violation $violation) {
        assert('$this->current_run_id !== null');
        assert('$this->current_rule_id !== null');
        $violation_id = $this->violation_id($violation);
        if ($violation_id === null) {
            $this->builder()
                ->insert($this->violation_table())
                ->values(array
                    ( "rule_id" => "?"
                    , "file" => "?"
                    , "line" => "?"
                    , "first_seen" => "?"
                    , "last_seen" => "?"
                    ))
                ->setParameter(0, $this->current_rule_id)
                ->setParameter(1, $violation->filename())
                ->setParameter(2, $violation->line())
                ->setParameter(3, $this->current_run_id)
                ->setParameter(4, $this->current_run_id)
                ->execute();
            $violation_id = (int)$this->connection->lastInsertId();
        }
        else {
            $this->builder()
                ->update($this->violation_table())
                ->set("last_seen", "?")
                ->where("id = ?")
                ->setParameter(0, $this->current_run_id)
                ->setParameter(1, $violation_id)
                ->execute();
        }

        $this->builder()
            ->insert($this->violation_location_table())
            ->values(array
                ( "violation_id" => "?"
                , "run_id"  => "?"
                , "line_no" => "?"
                ))
            ->setParameter(0, $violation_id)
            ->setParameter(1, $this->current_run_id)
            ->setParameter(2, $violation->line_no())
            ->execute();
    }

    // Helpers


    /**
     * @param   Rule    $rule
     * @return  int|null
     */
    protected function rule_id(Rule $rule) {
        $res = $this->builder()
            ->select("id")
            ->from($this->rule_table())
            ->where("rule = ?")
            ->setParameter(0, $rule->pprint())
            ->execute()
            ->fetch();
        if ($res) {
            return (int)$res["id"];
        }
        else {
            return null;
        }
    }

    /**
     * @param   Variable $var
     * @return  int|null
     */
    protected function variable_id(Variable $var) {
        $res = $this->builder()
            ->select("id")
            ->from($this->variable_table())
            ->where($this->builder()->expr()->andX
                ( "name = ?"
                , "meaning = ?"
                ))
            ->setParameter(0, $var->name())
            ->setParameter(1, $var->meaning())
            ->execute()
            ->fetch();
        if ($res) {
            return (int)$res["id"];
        }
        else {
            return null;
        }
    }

    protected function upsert_variable(Variable $var) {
        $var_id = $this->variable_id($var);
        if ($var_id === null) {
            $var_id = $this->insert_variable($var);            
        }
        else {
            $this->update_variable($var, $var_id);
        }
        return $var_id;
    }

    protected function insert_variable(Variable $var) {
        assert('$this->current_run_id !== null');
        $this->builder()
            ->insert($this->variable_table())
            ->values(array
                ( "name" => "?"
                , "meaning" => "?"
                , "first_seen" => "?"
                , "last_seen" => "?"
                ))
            ->setParameter(0, $var->name())
            ->setParameter(1, $var->meaning())
            ->setParameter(2, $this->current_run_id)
            ->setParameter(3, $this->current_run_id)
            ->execute();
        return (int)$this->connection->lastInsertId();
    }

    protected function update_variable(Variable $var, $var_id) {
        assert('is_integer($var_id)');
        assert('$this->current_run_id !== null');
        $this->builder()
            ->update($this->variable_table())
            ->set("last_seen", "?")
            ->where("id = ?")
            ->setParameter(0, $this->current_run_id)
            ->setParameter(1, $var_id)
            ->execute();
    }

    /**
     * @param   Violation   $violation
     * @return  int|null
     */
    protected function violation_id(Violation $violation) {
        $res = $this->builder()
            ->select("id")
            ->from($this->violation_table())
            ->where($this->builder()->expr()->andX
                ( "rule_id = ?"
                , "file = ?"
                , "line = ?"
                ))
            ->setParameter(0, $this->current_rule_id)
            ->setParameter(1, $violation->filename())
            ->setParameter(2, $violation->line())
            ->execute()
            ->fetch();
        if ($res) {
            return (int)$res["id"];
        }
        else {
            return null;
        }
    }

    // Names

    public function run_table() {
        return "runs";    
    }

    public function variable_table() {
        return "variables";
    }

    public function rule_table() {
        return "rules";
    }

    public function violation_table() {
        return "violations";
    }

    public function violation_location_table() {
        return "violation_locations";
    }

    // Creation of database.

    public function init_database_schema() {
        $schema = new Schema\Schema();

        $run_table = $schema->createTable($this->run_table());
        $run_table->addColumn
            ("id", "integer"
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $run_table->addColumn
            ( "commit_hash", "string"
            , array("notnull" => true)
            );
        // TODO: maybe add time
        // TODO: do we need some other meta information per run of the analysis? 
        $run_table->setPrimaryKey(array("id"));


        $rule_table = $schema->createTable($this->rule_table());
        $rule_table->addColumn
            ( "id", "integer"
            , array("notnull" => true)
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $rule_table->addColumn
            ( "rule", "string"
            , array("notnull" => true)
            );
        $rule_table->addColumn
            ( "first_seen", "integer"
            , array("notnull" => true)
            );
        $rule_table->addColumn
            ( "last_seen", "integer"
            , array("notnull" => true)
            );
        $rule_table->setPrimaryKey(array("id"));
        $rule_table->addUniqueIndex(array("rule"));
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

        
        $variable_table = $schema->createTable($this->variable_table());
        $variable_table->addColumn
            ( "id", "integer"
            , array("notnull" => true)
            );
        $variable_table->addColumn
            ( "name", "string"
            , array("notnull" => true)
            );
        $variable_table->addColumn
            ( "meaning", "string"
            , array("notnull" => true)
            );
        $variable_table->addColumn
            ( "first_seen", "integer"
            , array("notnull" => true)
            );
        $variable_table->addColumn
            ( "last_seen", "integer"
            , array("notnull" => true)
            );
        $variable_table->setPrimaryKey(array("id"));


        $violation_table = $schema->createTable($this->violation_table());
        $violation_table->addColumn
            ( "id", "integer"
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $violation_table->addColumn
            ( "rule_id", "integer"
            , array("notnull" => true)
            );
        $violation_table->addColumn
            ( "file", "string"
            , array("notnull" => true)
            );
        $violation_table->addColumn
            ( "line", "string"
            , array("notnull" => true)
            );
        $violation_table->addColumn
            ( "first_seen", "integer"
            , array("notnull" => true)
            );
        $violation_table->addColumn
            ( "last_seen", "integer"
            , array("notnull" => true)
            );
        $violation_table->setPrimaryKey(array("id"));
        $violation_table->addUniqueIndex(array("rule_id", "file", "line"));
        $violation_table->addForeignKeyConstraint
            ( $rule_table
            , array("rule_id")
            , array("id")
            );
        $violation_table->addForeignKeyConstraint
            ( $run_table
            , array("first_seen")
            , array("id")
            );
        $violation_table->addForeignKeyConstraint
            ( $run_table
            , array("last_seen")
            , array("id")
            );


        $violation_location_table = $schema->createTable($this->violation_location_table());

        $violation_location_table->addColumn
            ( "id", "integer"
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $violation_location_table->addColumn
            ( "violation_id", "integer"
            , array("notnull" => true)
            );
        $violation_location_table->addColumn
            ( "run_id", "integer"
            , array("notnull" => true)
            );
        $violation_location_table->addColumn
            ( "line_no", "integer"
            , array("notnull" => true)
            );
        $violation_location_table->setPrimaryKey(array("id"));
        $violation_location_table->addForeignKeyConstraint
            ( $violation_table
            , array("violation_id")
            , array("id")
            );
        $violation_location_table->addForeignKeyConstraint
            ( $run_table
            , array("run_id")
            , array("id")
            );

        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }
}
