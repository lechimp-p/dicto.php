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

use Lechimp\Dicto\Report\ResultDB;

class IndexDBFactory {
    /**
     * Create a new database for index at path.
     *
     * @param   string  $path
     * @throws  \RuntimeException   if database already exists.
     * @return  IndexDB
     */
    public function build_index_db($path) {
        if (file_exists($path)) {
            throw new \RuntimeException("File at '$path' already exists, can't build database.");
        }
        $connection = DB::sqlite_connection($path);
        $db = new IndexDB($connection);
        $db->init_sqlite_regexp();
        $db->init_database_schema();
        return $db;
    }

    /**
     * Check if an index database exists.
     *
     * @param   string  $path
     * @return  bool
     */
    public function index_db_exists($path) {
        return file_exists($path);
    }

    /**
     * Load existing index database. 
     *
     * @param   string  $path
     * @throws  \RuntimeException   if file does not exist
     * @return  IndexDB 
     */
    public function load_index_db($path) {
        if (!$this->index_db_exists($path)) {
            throw new \RuntimeException("There is no index database at '$path'");
        }
        $connection = DB::sqlite_connection($path);
        $db = new IndexDB($connection);
        $db->init_sqlite_regexp();
        return $db;
    }
}
