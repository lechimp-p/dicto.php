<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Analysis\Consts;
use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Indexer\Insert;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

class DB implements Insert, Query {
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * @return \Doctrine\DBAL\Query\Builder
     */
    public function builder() {
        return $this->connection->createQueryBuilder();
    }

    // Implementation of Insert interface.

    /**
     * @inheritdoc
     */
    public function entity($type, $name, $file, $start_line, $end_line, $source) {
        assert('in_array($type, \\Lechimp\\Dicto\\Analysis\\Consts::$ENTITY_TYPES)');
        assert('is_string($name)');
        assert('is_string($file)');
        assert('is_int($start_line)');
        assert('is_int($end_line)');
        assert('is_string($source)');
        $this->builder()
            ->insert($this->entity_table())
            ->values(array
                ( "type" => "?"
                , "name" => "?"
                , "file" => "?"
                , "start_line" => "?"
                , "end_line" => "?"
                , "source" => "?"
                ))
            ->setParameter(0, $type)
            ->setParameter(1, $name)
            ->setParameter(2, $file)
            ->setParameter(3, $start_line)
            ->setParameter(4, $end_line)
            ->setParameter(5, $source)
            ->execute();
        return (int)$this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function reference($type, $name, $file, $line) {
        assert('in_array($type, \\Lechimp\\Dicto\\Analysis\\Consts::$ENTITY_TYPES)');
        assert('is_string($name)');
        assert('is_string($file)');
        assert('is_int($line)');
        $this->builder()
            ->insert($this->reference_table())
            ->values(array
                ( "type" => "?"
                , "name" => "?"
                , "file" => "?"
                , "line" => "?"
                ))
            ->setParameter(0, $type)
            ->setParameter(1, $name)
            ->setParameter(2, $file)
            ->setParameter(3, $line)
            ->execute();
        return (int)$this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function dependency($dependent_id, $dependency_id, $file, $line, $source_line) {
        assert('is_int($dependent_id)');
        assert('is_int($dependency_id)');
        assert('is_string($file)');
        assert('is_int($line)');
        assert('is_string($source_line)');
        $this->builder()
            ->insert($this->dependencies_table())
            ->values(array
                ( "dependent_id" => "?"
                , "dependency_id" => "?"
                , "file" => "?"
                , "line" => "?"
                , "source_line" => "?"
                ))
            ->setParameter(0, $dependent_id)
            ->setParameter(1, $dependency_id)
            ->setParameter(2, $file)
            ->setParameter(3, $line)
            ->setParameter(4, $source_line)
            ->execute();
    }

    /**
     * @inheritdoc
     */
    public function invocation($invoker_id, $invokee_id, $file, $line, $source_line) {
        assert('is_int($invoker_id)');
        assert('is_int($invokee_id)');
        assert('is_string($file)');
        assert('is_int($line)');
        assert('is_string($source_line)');
        $this->builder()
            ->insert($this->invocations_table())
            ->values(array
                ( "invoker_id" => "?"
                , "invokee_id" => "?"
                , "file" => "?"
                , "line" => "?"
                , "source_line" => "?"
                ))
            ->setParameter(0, $invoker_id)
            ->setParameter(1, $invokee_id)
            ->setParameter(2, $file)
            ->setParameter(3, $line)
            ->setParameter(4, $source_line)
            ->execute();
    }

    // Naming

    public function entity_table() {
        return "entities";
    } 

    public function reference_table() {
        return "refs";
    }

    public function dependencies_table() {
        return "dependencies";
    }

    public function invocations_table() {
        return "invocations";
    }

    /**
     * Initialize REGEXP for sqlite.
     */
    public function init_sqlite_regexp() {
        $pdo = $this->connection->getWrappedConnection();
        $pdo->sqliteCreateFunction("regexp", function($pattern, $data) {
            return preg_match("%$pattern%", $data) > 0;
        });
    }

    // Creation of database.

    public function init_database_schema() {
        $schema = new Schema\Schema();

        $entity_table = $schema->createTable($this->entity_table());
        $entity_table->addColumn
            ("id", "integer"
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $entity_table->addColumn
            ("type", "string"
            , array("notnull" => true)
            );
        $entity_table->addColumn
            ("name", "string"
            , array("notnull" => true)
            );
        $entity_table->addColumn
            ("file", "string"
            , array("notnull" => true)
            );
        $entity_table->addColumn
            ("start_line", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $entity_table->addColumn
            ("end_line", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $entity_table->addColumn
            ("source", "text"
            , array("notnull" => true)
            );
        $entity_table->setPrimaryKey(array("id"));

        $reference_table = $schema->createTable($this->reference_table());
        $reference_table->addColumn
            ( "id", "integer"
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $reference_table->addColumn
            ("type", "string"
            , array("notnull" => true)
            );
        $reference_table->addColumn
            ("name", "string"
            , array("notnull" => true)
            );
        $reference_table->addColumn
            ("file", "string"
            , array("notnull" => true)
            );
        $reference_table->addColumn
            ("line", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $reference_table->setPrimaryKey(array("id"));

        $dependencies_table = $schema->createTable($this->dependencies_table());
        $dependencies_table->addColumn
            ( "dependent_id", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $dependencies_table->addColumn
            ( "dependency_id", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $dependencies_table->addColumn
            ("file", "string"
            , array("notnull" => true)
            );
        $dependencies_table->addColumn
            ("line", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $dependencies_table->addColumn
            ("source_line", "text"
            , array("notnull" => true)
            );
        $dependencies_table->addForeignKeyConstraint
            ( $entity_table
            , array("dependent_id")
            , array("id")
            );
        $dependencies_table->addForeignKeyConstraint
            ( $reference_table
            , array("dependency_id")
            , array("id")
            );

        $invocations_table = $schema->createTable($this->invocations_table());
        $invocations_table->addColumn
            ( "invoker_id", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $invocations_table->addColumn
            ( "invokee_id", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $invocations_table->addColumn
            ("file", "string"
            , array("notnull" => true)
            );
        $invocations_table->addColumn
            ("line", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $invocations_table->addColumn
            ("source_line", "text"
            , array("notnull" => true)
            );
        $invocations_table->addForeignKeyConstraint
            ( $entity_table
            , array("invoker_id")
            , array("id")
            );
        $invocations_table->addForeignKeyConstraint
            ( $reference_table
            , array("invokee_id")
            , array("id")
            );

        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }
}
