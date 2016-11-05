<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Indexer\InsertTwice;
use Lechimp\Dicto\Graph;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class IndexDBTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
    }

    public function test_read_write_db() {
        $db1 = new Graph\IndexDB();
        $db2 = new Graph\IndexDB();
        $db = new InsertTwice($db1, $db2);

        $file = $db->_file("source.php", "<?php echo \"Hello World!\";");
        $class = $db->_class("AClass", $file, 1,1);
        $interface = $db->_class("AnInterface", $file, 1,1);
        $db->_method("a_method", $class, $file, 1,1);
        $db->_method("another_method", $interface, $file, 1,1);
        $db->_function("a_function", $file, 1,1);
        $db->_global("a_global");
        $db->_language_construct("@");
        $method_reference = $db->_method_reference("a_method", $file, 1, 2);
        $db->_function_reference("a_function", $file, 1, 2);
        $db->_relation($class, "relates to", $method_reference, $file, 1);

        $this->assertEquals($db1->node(0), $db2->node(0));
        $this->assertEquals($db1, $db2);
    }
}
