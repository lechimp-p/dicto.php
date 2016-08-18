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

use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Analysis\CompilesVars;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Indexer\CachesReferences;
use Doctrine\DBAL\Schema as S;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

class IndexDB extends DB implements Insert, Query {
//    use CachesReferences;

    // Implementation of Insert interface.

    /**
     * @inheritdoc
     */
    public function name($name, $type) {
        assert('is_string($name)');
        assert('\\Lechimp\\Dicto\\Variables\\Variable::is_type($type)');

        $name_id = $this->get_int_id($name, $this->name_table(), "name");
        if ($name_id !== null) {
            return $name_id;
        }

        $this->builder()
            ->insert($this->name_table())
            ->values(array
                ( "name" => "?"
                , "type" => "?"
                ))
            ->setParameter(0, $name)
            ->setParameter(1, $type)
            ->execute();
        return (int)$this->connection->lastInsertId();
    }

    /**
     * Store a filename or just get its id if the file is already stored.
     *
     * @param   string      $path
     * @return  int
     */
    public function file($path) {
        assert('is_string($path)');

        $file_id = $this->get_int_id($path, $this->file_table(), "path");
        if ($file_id !== null) {
            return $file_id;
        }

        $this->builder()
            ->insert($this->file_table())
            ->values(array
                ( "path" => "?"
                ))
            ->setParameter(0, $path)
            ->execute();
        return (int)$this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function source($path, $content) {
        assert('is_string($content)');

        $file_id = $this->file($path);

        $stmt = $this->builder()
            ->insert($this->source_table())
            ->values(array
                ( "file" => "?"
                , "line" => "?"
                , "source" => "?"
                ))
            ->setParameter(0, $file_id);
        $line = 1;
        foreach (explode("\n", $content) as $source) {
            $stmt
                ->setParameter(1, $line)
                ->setParameter(2, $source)
                ->execute();
            $line++;
        }
    }

    /**
     * @inheritdoc
     */
    public function definition($name, $type, $file, $start_line, $end_line) {
        assert('is_int($start_line)');
        assert('is_int($end_line)');

        $name_id = $this->name($name, $type);
        $file_id = $this->file($file);

        $this->builder()
            ->insert($this->definition_table())
            ->values(array
                ( "name" => "?"
                , "file" => "?"
                , "start_line" => "?"
                , "end_line" => "?"
                ))
            ->setParameter(0, $name_id)
            ->setParameter(1, $file_id)
            ->setParameter(2, $start_line)
            ->setParameter(3, $end_line)
            ->execute();
        return $name_id;
    }

    /**
     * @inheritdoc
     */
    public function relation($name_left, $name_right, $which, $file, $line) {
        assert('is_string($name_left)');
        assert('is_string($name_right)');
        assert('is_string($which)');
        assert('is_int($line)');

        $id_left = $this->get_int_id($name_left, $this->name_table(), "name");
        $id_right = $this->get_int_id($name_right, $this->name_table(), "name");
        $file_id = $this->get_int_id($file, $this->file_table(), "path");

        assert('is_int($id_left)');
        assert('is_int($id_right)');
        assert('is_int($file_id)');

        $this->builder()
            ->insert($this->relation_table())
            ->values(array
                ( "name_left" => "?"
                , "name_right" => "?"
                , "which" => "?"
                , "file" => "?"
                , "line" => "?"
                ))
            ->setParameter(0, $id_left)
            ->setParameter(1, $id_right)
            ->setParameter(2, $which)
            ->setParameter(3, $file_id)
            ->setParameter(4, $line)
            ->execute();
    }

    /**
     * Get the numeric id for the stringy id in table by using id_column
     * as column name.
     *
     * @param   string  $id
     * @param   string  $table
     * @param   string  $id_colum
     * @return  int|null
     */
    protected function get_int_id($id, $table, $id_column) {
        $res = $this->builder()
            ->select("id")
            ->from($table)
            ->where($this->builder()->expr()->andX
                ( "$id_column = ?"
                ))
            ->setParameter(0, $id)
            ->execute()
            ->fetch();
        if ($res) {
            return (int)$res["id"];
        }
        else {
            return null;
        }
    }

    // Naming

    public function name_table() {
        return "names";
    }

    public function init_name_table(S\Schema $schema) {
        $name_table = $schema->createTable($this->name_table());
        $name_table->addColumn
            ("id", "integer"
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $name_table->addColumn
            ( "name", "string"
            , array("notnull" => true)
            );
        // TODO: insert namespace column here
        $name_table->addColumn
            ( "type", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $name_table->setPrimaryKey(array("id"));
        $name_table->addUniqueIndex(array("name"));
        return $name_table;
    }

    public function file_table() {
        return "files";
    }

    public function init_file_table(S\Schema $schema) {
        $file_table = $schema->createTable($this->file_table());
        $file_table->addColumn
            ("id", "integer"
            , array("notnull" => true, "unsigned" => true, "autoincrement" => true)
            );
        $file_table->addColumn
            ( "path", "string"
            , array("notnull" => true)
            );
        $file_table->setPrimaryKey(array("id"));
        return $file_table;
    }

    public function source_table() {
        return "source";
    }

    public function init_source_table(S\Schema $schema, S\Table $file_table) {
        $source_table = $schema->createTable($this->source_table());
        $source_table->addColumn
            ( "file", "integer"
            , array("notnull" => true)
            );
        $source_table->addColumn
            ( "line", "integer"
            , array("notnull" => true, "unsigned" => true)
            );
        $source_table->addColumn
            ( "source", "string"
            , array("notnull" => true)
            );
        $source_table->setPrimaryKey(array("file", "line"));
        $source_table->addForeignKeyConstraint
            ( $file_table
            , array("file")
            , array("id")
            );
        return $source_table;
    }

    public function definition_table() {
        return "definitions";
    }

    public function init_definition_table(S\Schema $schema, S\Table $name_table, S\Table $source_table) {
        $definition_table = $schema->createTable($this->definition_table());
        $definition_table->addColumn
            ( "name", "integer"
            , array("notnull" => true)
            );
        $definition_table->addColumn
            ( "file", "integer"
            , array("notnull" => true)
            );
        $definition_table->addColumn
            ( "start_line", "integer"
            , array("notnull" => true)
            );
        $definition_table->addColumn
            ( "end_line", "integer"
            , array("notnull" => true)
            );
        $definition_table->setPrimaryKey(array("name"));
        $definition_table->addForeignKeyConstraint
            ( $name_table
            , array("name")
            , array("id")
            );
        $definition_table->addForeignKeyConstraint
            ( $source_table
            , array("file", "start_line")
            , array("file", "line")
            );
        $definition_table->addForeignKeyConstraint
            ( $source_table
            , array("file", "end_line")
            , array("file", "line")
            );
        return $definition_table;
    }

    public function reference_table() {
        return "refs";
    }

    public function init_reference_table(S\Schema $schema, S\Table $name_table, S\Table $source_table) {
        $reference_table = $schema->createTable($this->reference_table());
        $reference_table->addColumn
            ( "name", "integer"
            , array("notnull" => true)
            );
        $reference_table->addColumn
            ( "file", "integer"
            , array("notnull" => true)
            );
        $reference_table->addColumn
            ( "line", "integer"
            , array("notnull" => true)
            );
        $reference_table->setPrimaryKey(array("name", "file", "line"));
        $reference_table->addForeignKeyConstraint
            ( $name_table
            , array("name")
            , array("id")
            );
        $reference_table->addForeignKeyConstraint
            ( $source_table
            , array("file", "line")
            , array("file", "line")
            );
        return $reference_table;
    }

    public function relation_table() {
        return "relations";
    }

    public function init_relation_table(S\Schema $schema, S\Table $name_table, S\Table $source_table) {
        $relation_table = $schema->createTable($this->relation_table());
        $relation_table->addColumn
            ( "name_left", "integer"
            , array("notnull" => true)
            );
        $relation_table->addColumn
            ( "name_right", "integer"
            , array("notnull" => true)
            );
        $relation_table->addColumn
            ( "which", "string"
            , array("notnull" => true)
            );
        $relation_table->addColumn
            ( "file", "integer"
            , array("notnull" => true)
            );
        $relation_table->addColumn
            ( "line", "integer"
            , array("notnull" => true)
            );
        $relation_table->setPrimaryKey(array("name_left", "name_right", "which"));
        $relation_table->addForeignKeyConstraint
            ( $name_table
            , array("name_left")
            , array("id")
            );
        $relation_table->addForeignKeyConstraint
            ( $name_table
            , array("name_right")
            , array("id")
            );
        $relation_table->addForeignKeyConstraint
            ( $source_table
            , array("file", "line")
            , array("file", "line")
            );
        return $relation_table;
    }

    // Creation of database.

    public function init_database_schema() {
        $schema = new S\Schema();

        $name_table = $this->init_name_table($schema);
        $file_table = $this->init_file_table($schema);
        $source_table = $this->init_source_table($schema, $name_table);
        $this->init_definition_table($schema, $name_table, $source_table);
        $this->init_reference_table($schema, $name_table, $source_table);
        $this->init_relation_table($schema, $name_table, $source_table);

        $sync = new SingleDatabaseSynchronizer($this->connection);
        $sync->createSchema($schema);
    }
}
