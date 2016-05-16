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

use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Analysis\CompilesVars;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Indexer\CachesReferences;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

class DB implements Insert, Query {
    use CachesReferences;
    use CompilesVars;

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
        assert('\\Lechimp\\Dicto\\Variables\\Variable::is_type($type)');
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
        assert('\\Lechimp\\Dicto\\Variables\\Variable::is_type($type)');
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
    public function relation($name, $entity_id, $reference_id, $file, $line, $source_line) {
        assert('is_string($name)');
        assert('is_int($entity_id)');
        assert('is_int($reference_id)');
        assert('is_string($file)');
        assert('is_int($line)');
        assert('is_string($source_line)');
        $this->builder()
            ->insert($this->relations_table())
            ->values(array
                ( "name" => "?"
                , "entity_id" => "?"
                , "reference_id" => "?"
                , "file" => "?"
                , "line" => "?"
                , "source_line" => "?"
                ))
            ->setParameter(0, $name)
            ->setParameter(1, $entity_id)
            ->setParameter(2, $reference_id)
            ->setParameter(3, $file)
            ->setParameter(4, $line)
            ->setParameter(5, $source_line)
            ->execute();
    }

    // Naming

    public function entity_table() {
        return "entities";
    } 

    public function reference_table() {
        return "refs";
    }

    public function relations_table() {
        return "relations";
    }

    /**
     * Initialize REGEXP for sqlite.
     */
    public function init_sqlite_regexp() {
        $pdo = $this->connection->getWrappedConnection();
        if (!($pdo instanceof \PDO)) {
            throw new \RuntimeException(
                "Expected wrapped connection to be PDO-object.");
        }
        $pdo->sqliteCreateFunction("regexp", function($pattern, $data) {
            return preg_match("%$pattern%", $data) > 0;
        });
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

        $relations_table = $schema->createTable($this->relations_table());
        $relations_table->addColumn
            ("name", "string"
            , array("notnull" => true)
            );
        $relations_table->addColumn
            ( "entity_id", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $relations_table->addColumn
            ( "reference_id", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $relations_table->addColumn
            ("file", "string"
            , array("notnull" => true)
            );
        $relations_table->addColumn
            ("line", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $relations_table->addColumn
            ("source_line", "text"
            , array("notnull" => true)
            );
        $relations_table->addForeignKeyConstraint
            ( $entity_table
            , array("entity_id")
            , array("id")
            );
        $relations_table->addForeignKeyConstraint
            ( $reference_table
            , array("reference_id")
            , array("id")
            );

        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }
}
