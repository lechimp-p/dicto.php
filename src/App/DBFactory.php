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

use Doctrine\DBAL\DriverManager;

class DBFactory {
    /**
     * @param   string  $path
     * @throws  \RuntimeException   when database already exists.
     * @return  IndexDB
     */
    public function build_index_db($path) {
        if (file_exists($path)) {
            throw new \RuntimeException("File at '$path' already exists, can't build database.");
        }
        $connection = $this->build_connection($path);
        $db = new IndexDB($connection);
        $db->init_sqlite_regexp();
        $db->init_database_schema();
        return $db;
    }

    protected function build_connection($path) {
        assert('is_string($path)');
        return DriverManager::getConnection
            ( array
                ( "driver" => "pdo_sqlite"
                , "memory" => false
                , "path" => $path
                )
            );
    }
}
