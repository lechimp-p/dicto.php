<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\DB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

abstract class DB
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return \Doctrine\DBAL\Query\Builder
     */
    public function builder()
    {
        return $this->connection->createQueryBuilder();
    }

    /**
     * @return Connection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Initialize REGEXP for sqlite.
     */
    public function init_sqlite_regexp()
    {
        $pdo = $this->connection->getWrappedConnection();
        if (!($pdo instanceof \PDO)) {
            throw new \RuntimeException(
                "Expected wrapped connection to be PDO-object."
            );
        }
        $pdo->sqliteCreateFunction("regexp", function ($pattern, $data) {
            return preg_match("%$pattern%", $data) > 0;
        });
    }

    // Creation of database.

    public function is_inited()
    {
        $res = $this->builder()
            ->select("COUNT(*)")
            ->from("sqlite_master")
            ->where("type = 'table'")
            ->execute()
            ->fetchColumn();
        return $res > 0;
    }

    public function maybe_init_database_schema()
    {
        if (!$this->is_inited()) {
            $this->init_database_schema();
        }
    }

    abstract public function init_database_schema();

    /**
     * Build pdo_sqlite connection to some file if path is given or
     * to memory instead.
     *
     * @param   string|null $path
     * @return  Connection
     */
    public static function sqlite_connection($path = null)
    {
        assert('is_string($path) || is_null($path)');
        if ($path !== null) {
            return DriverManager::getConnection(["driver" => "pdo_sqlite"
                , "memory" => false
                , "path" => $path
                ]);
        } else {
            return DriverManager::getConnection(["driver" => "pdo_sqlite"
                , "memory" => true
                ]);
        }
    }
}
