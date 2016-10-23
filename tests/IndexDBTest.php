<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\IndexDB;
use Lechimp\Dicto\Graph;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class _GraphIndexDB extends Graph\IndexDB {
    public function flush_caches() {
        $this->globals = [];
        $this->language_constructs = [];
        $this->method_references = [];
        $this->function_references = [];
    }
}

class _IndexDB extends IndexDB {
    protected function build_graph_index_db() {
        return new _GraphIndexDB();
    }
}

class IndexDBTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->connection = DriverManager::getConnection(
            [ "driver" => "pdo_sqlite"
            , "memory" => true
            ]);
        $this->db1 = new _IndexDB($this->connection);
        $this->db1->init_database_schema();
        $this->db2 = new _IndexDB($this->connection);
    }

    public function test_read_write_db() {
        $db1 = new _GraphIndexDB();
        $file = $db1->_file("source.php", "<?php echo \"Hello World!\";");
        $class = $db1->_class("AClass", $file, 1,1);
        $interface = $db1->_class("AnInterface", $file, 1,1);
        $db1->_method("a_method", $class, $file, 1,1);
        $db1->_method("another_method", $interface, $file, 1,1);
        $db1->_function("a_function", $file, 1,1);
        $db1->_global("a_global");
        $db1->_language_construct("@");
        $method_reference = $db1->_method_reference("a_method", $file, 1, 2);
        $db1->_function_reference("a_function", $file, 1, 2);
        $db1->_relation($class, "relates to", $method_reference, $file, 1);

        $this->db1->write_index($db1);
        $db1->flush_caches();
        $db2 = $this->db2->read_index();

        $this->assertEquals($db1, $db2);
    }
}
