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

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    // Implementation of Insert interface.

    /**
     * @inheritdoc
     */
    public function entity($type, $name, $file, $start_line, $end_line, $source) {
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
