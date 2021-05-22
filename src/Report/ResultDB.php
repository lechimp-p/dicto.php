<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Report;

use Lechimp\Dicto\Analysis\Listener;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\DB\DB;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules\Rule;
use Lechimp\Dicto\Variables\Variable;
use Doctrine\DBAL\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

class ResultDB extends DB implements Listener
{
    /**
     * @var int|null
     */
    private $current_run_id = null;

    /**
     * @var int|null
     */
    private $current_rule_id = null;

    // Analysis\Listener implementation

    /**
     * Announce to start a new run of the analysis now.
     *
     * @param   string  $commit_hash
     * @return  null
     */
    public function begin_run($commit_hash)
    {
        assert('is_string($commit_hash)');
        $this->builder()
            ->insert("runs")
            ->values(array( "commit_hash" => "?"
                ))
            ->setParameter(0, $commit_hash)
            ->execute();
        $this->current_run_id = (int) $this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function end_run()
    {
    }

    /**
     * @inheritdoc
     */
    public function begin_ruleset(Ruleset $rule)
    {
        // Nothing to do here...
    }

    /**
     * @inheritdoc
     */
    public function end_ruleset()
    {
        // Nothing to do here...
    }

    /**
     * @inheritdoc
     */
    public function begin_rule(Rule $rule)
    {
        assert('$this->current_run_id !== null');
        $rule_id = $this->rule_id($rule);
        if ($rule_id === null) {
            $this->builder()
                ->insert("rules")
                ->values(array( "rule" => "?"
                    , "first_seen" => "?"
                    , "last_seen" => "?"
                    , "explanation" => "?"
                    ))
                ->setParameter(0, $rule->pprint())
                ->setParameter(1, $this->current_run_id)
                ->setParameter(2, $this->current_run_id)
                ->setParameter(3, $rule->explanation())
                ->execute();
            $rule_id = (int) $this->connection->lastInsertId();
        } else {
            $this->builder()
                ->update("rules")
                ->set("last_seen", "?")
                ->set("explanation", "?")
                ->where("id = ?")
                ->setParameter(0, $this->current_run_id)
                ->setParameter(1, $rule->explanation())
                ->setParameter(2, $rule_id)
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
    public function end_rule()
    {
        $this->current_rule_id = null;
    }

    /**
     * @inheritdoc
     */
    public function report_violation(Violation $violation)
    {
        assert('$this->current_run_id !== null');
        assert('$this->current_rule_id !== null');
        $violation_id = $this->violation_id($violation);
        if ($violation_id === null) {
            $this->builder()
                ->insert("violations")
                ->values(array( "rule_id" => "?"
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
            $violation_id = (int) $this->connection->lastInsertId();
        } else {
            $this->builder()
                ->update("violations")
                ->set("last_seen", "?")
                ->where("id = ?")
                ->setParameter(0, $this->current_run_id)
                ->setParameter(1, $violation_id)
                ->execute();
        }

        $this->builder()
            ->insert("violation_locations")
            ->values(array( "violation_id" => "?"
                , "run_id" => "?"
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
    protected function rule_id(Rule $rule)
    {
        $res = $this->builder()
            ->select("id")
            ->from("rules")
            ->where("rule = ?")
            ->setParameter(0, $rule->pprint())
            ->execute()
            ->fetch();
        if ($res) {
            return (int) $res["id"];
        } else {
            return null;
        }
    }

    /**
     * @param   Variable $var
     * @return  int|null
     */
    protected function variable_id(Variable $var)
    {
        $res = $this->builder()
            ->select("id")
            ->from("variables")
            ->where($this->builder()->expr()->andX(
                    "name = ?",
                    "meaning = ?"
                ))
            ->setParameter(0, $var->name())
            ->setParameter(1, $var->meaning())
            ->execute()
            ->fetch();
        if ($res) {
            return (int) $res["id"];
        } else {
            return null;
        }
    }

    protected function upsert_variable(Variable $var)
    {
        $var_id = $this->variable_id($var);
        if ($var_id === null) {
            $var_id = $this->insert_variable($var);
        } else {
            $this->update_variable($var_id);
        }
        return $var_id;
    }

    protected function insert_variable(Variable $var)
    {
        assert('$this->current_run_id !== null');
        $this->builder()
            ->insert("variables")
            ->values(array( "name" => "?"
                , "meaning" => "?"
                , "first_seen" => "?"
                , "last_seen" => "?"
                ))
            ->setParameter(0, $var->name())
            ->setParameter(1, $var->meaning())
            ->setParameter(2, $this->current_run_id)
            ->setParameter(3, $this->current_run_id)
            ->execute();
        return (int) $this->connection->lastInsertId();
    }

    protected function update_variable($var_id)
    {
        assert('is_integer($var_id)');
        assert('$this->current_run_id !== null');
        $this->builder()
            ->update("variables")
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
    protected function violation_id(Violation $violation)
    {
        $res = $this->builder()
            ->select("id")
            ->from("violations")
            ->where($this->builder()->expr()->andX(
                    "rule_id = ?",
                    "file = ?",
                    "line = ?"
                ))
            ->setParameter(0, $this->current_rule_id)
            ->setParameter(1, $violation->filename())
            ->setParameter(2, $violation->line())
            ->execute()
            ->fetch();
        if ($res) {
            return (int) $res["id"];
        } else {
            return null;
        }
    }

    // Creation of database.

    protected function init_run_table(Schema\Schema $schema)
    {
        $run_table = $schema->createTable("runs");
        $run_table->addColumn(
                "id",
                "integer",
                array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $run_table->addColumn(
                "commit_hash",
                "string",
                array("notnull" => true)
            );
        // TODO: maybe add time
        // TODO: do we need some other meta information per run of the analysis?
        // TODO: Might be a good idea to store config and rules file here
        $run_table->setPrimaryKey(array("id"));

        return $run_table;
    }

    protected function init_rule_table(Schema\Schema $schema, Schema\Table $run_table)
    {
        $rule_table = $schema->createTable("rules");
        $rule_table->addColumn(
                "id",
                "integer",
                array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $rule_table->addColumn(
                "rule",
                "string",
                array("notnull" => true)
            );

        $rule_table->addColumn(
                "explanation",
                "string",
                array("notnull" => false)
            );
        $rule_table->addColumn(
                "first_seen",
                "integer",
                array("notnull" => true)
            );
        $rule_table->addColumn(
                "last_seen",
                "integer",
                array("notnull" => true)
            );
        $rule_table->setPrimaryKey(array("id"));
        $rule_table->addUniqueIndex(array("rule"));
        $rule_table->addForeignKeyConstraint(
                $run_table,
                array("first_seen"),
                array("id")
            );
        $rule_table->addForeignKeyConstraint(
                $run_table,
                array("last_seen"),
                array("id")
            );

        return $rule_table;
    }

    public function init_variable_table(Schema\Schema $schema)
    {
        $variable_table = $schema->createTable("variables");
        $variable_table->addColumn(
                "id",
                "integer",
                array("notnull" => true)
            );
        $variable_table->addColumn(
                "name",
                "string",
                array("notnull" => true)
            );
        $variable_table->addColumn(
                "meaning",
                "string",
                array("notnull" => true)
            );
        // TODO: Some field for explanation is missing here.
        $variable_table->addColumn(
                "first_seen",
                "integer",
                array("notnull" => true)
            );
        $variable_table->addColumn(
                "last_seen",
                "integer",
                array("notnull" => true)
            );
        $variable_table->setPrimaryKey(array("id"));

        return $variable_table;
    }

    protected function init_violation_table(Schema\Schema $schema, Schema\Table $run_table, Schema\Table $rule_table)
    {
        $violation_table = $schema->createTable("violations");
        $violation_table->addColumn(
                "id",
                "integer",
                array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $violation_table->addColumn(
                "rule_id",
                "integer",
                array("notnull" => true)
            );
        $violation_table->addColumn(
                "file",
                "string",
                array("notnull" => true)
            );
        $violation_table->addColumn(
                "line",
                "string",
                array("notnull" => true)
            );
        $violation_table->addColumn(
                "first_seen",
                "integer",
                array("notnull" => true)
            );
        $violation_table->addColumn(
                "last_seen",
                "integer",
                array("notnull" => true)
            );
        $violation_table->setPrimaryKey(array("id"));
        $violation_table->addUniqueIndex(array("rule_id", "file", "line"));
        $violation_table->addForeignKeyConstraint(
                $rule_table,
                array("rule_id"),
                array("id")
            );
        $violation_table->addForeignKeyConstraint(
                $run_table,
                array("first_seen"),
                array("id")
            );
        $violation_table->addForeignKeyConstraint(
                $run_table,
                array("last_seen"),
                array("id")
            );

        return $violation_table;
    }

    protected function init_violation_location_table(Schema\Schema $schema, Schema\Table $run_table, Schema\Table $violation_table)
    {
        $violation_location_table = $schema->createTable("violation_locations");
        $violation_location_table->addColumn(
                "id",
                "integer",
                array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $violation_location_table->addColumn(
                "violation_id",
                "integer",
                array("notnull" => true)
            );
        $violation_location_table->addColumn(
                "run_id",
                "integer",
                array("notnull" => true)
            );
        $violation_location_table->addColumn(
                "line_no",
                "integer",
                array("notnull" => true)
            );
        $violation_location_table->setPrimaryKey(array("id"));
        $violation_location_table->addForeignKeyConstraint(
                $violation_table,
                array("violation_id"),
                array("id")
            );
        $violation_location_table->addForeignKeyConstraint(
                $run_table,
                array("run_id"),
                array("id")
            );
        return $violation_location_table;
    }

    public function init_database_schema()
    {
        $schema = new Schema\Schema();

        $run_table = $this->init_run_table($schema);
        $rule_table = $this->init_rule_table($schema, $run_table);
        $this->init_variable_table($schema);
        $violation_table = $this->init_violation_table($schema, $run_table, $rule_table);
        $this->init_violation_location_table($schema, $run_table, $violation_table);

        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }
}
