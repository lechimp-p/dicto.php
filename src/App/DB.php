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
use Lechimp\Dicto\Indexer\Insert;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

class DB implements Insert {
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var \Doctrine\DBAL\Query\Builder
     */
    protected $builder;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
        $this->builder = $this->connection->createQueryBuilder();
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
        $this->builder
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
        return $this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function reference($type, $name, $file, $line) {
    }

    /**
     * @inheritdoc
     */
    public function dependency($dependent_id, $dependency_id, $file, $line, $source_line) {
    }

    /**
     * @inheritdoc
     */
    public function invocation($invoker_id, $invokee_id, $file, $line, $source_line) {
    }

    // Naming

    public function entity_table() {
        return "entities";
    } 

    public function reference_table() {
        return "references";
    }

    public function dependencies_table() {
        return "dependencies";
    }

    public function invocations_table() {
        return "invocations";
    }

    // Creation of database.

    public function create_database() {
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


        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }
}
