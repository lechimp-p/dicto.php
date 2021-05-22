<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Indexer\InsertTwice;
use Lechimp\Dicto\Graph;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class InsertTwiceTest extends \PHPUnit\Framework\TestCase
{
    public function test_first_second()
    {
        $db1 = new Graph\IndexDB();
        $db2 = new Graph\IndexDB();
        $db = new InsertTwice($db1, $db2);

        $this->assertSame($db1, $db->first());
        $this->assertSame($db2, $db->second());
    }

    public function test_read_write_db()
    {
        $db1 = new Graph\IndexDB();
        $db2 = new Graph\IndexDB();
        $db = new InsertTwice($db1, $db2);

        $file = $db->_file("source.php", "<?php echo \"Hello World!\";");
        $namespace = $db->_namespace("ANamespace");
        $class1 = $db->_class("AClass", $file, 1, 1);
        $class2 = $db->_class("AnotherClass", $file, 1, 1, $namespace);
        $interface1 = $db->_class("AnInterface", $file, 1, 1);
        $interface2 = $db->_class("AnotherInterface", $file, 1, 1, $namespace);
        $trait1 = $db->_class("AnTrait", $file, 1, 1);
        $trait2 = $db->_class("AnotherTrait", $file, 1, 1, $namespace);
        $db->_method("a_method", $class1, $file, 1, 1);
        $db->_method("a_method", $class2, $file, 1, 1);
        $db->_method("another_method", $interface1, $file, 1, 1);
        $db->_method("another_method", $interface2, $file, 1, 1);
        $db->_method("another_method", $trait1, $file, 1, 1);
        $db->_method("another_method", $trait2, $file, 1, 1);
        $db->_function("a_function", $file, 1, 1);
        $db->_function("another_function", $file, 1, 1, $namespace);
        $db->_global("a_global");
        $db->_language_construct("@");
        $method_reference = $db->_method_reference("a_method", $file, 1, 2);
        $db->_function_reference("a_function", $file, 1, 2);
        $db->_relation($class1, "relates to", $method_reference, $file, 1);
        $db->_relation($class2, "relates to", $method_reference, $file, 1);

        $this->assertEquals($db1->node(0), $db2->node(0));
        $this->assertEquals($db1, $db2);
    }
}
