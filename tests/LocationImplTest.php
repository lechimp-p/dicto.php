<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Indexer\LocationImpl;
use Lechimp\Dicto\Variables\Variable;
use PhpParser\Node\Stmt\Class_; 

class _LocationImpl extends LocationImpl {
    public function get_running_line_length() {
        return $this->running_line_length;
    }
    public function _init_running_line_length() {
        $this->init_running_line_length();
    }
}

class LocationImplTest extends \PHPUnit\Framework\TestCase {
    public function location($file, $content) {
        return new _LocationImpl($file, $content);
    }

    public function test_file_name() {
        $loc = $this->location("file.php", "");
        $this->assertEquals("file.php", $loc->_file_name());
    }

    public function test_file_content() {
        $loc = $this->location("file.php", "some_content");
        $this->assertEquals("some_content", $loc->_file_content());
    }
    public function test_in_entity_empty() {
        $loc = $this->location("file.php", "");
        $this->assertEquals(null, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(null, $loc->_class_interface_trait());
        $this->assertEquals(null, $loc->_function_method());
    }

    public function test_in_entites() {
        $loc = $this->location("file.php", "");
        $loc->push_entity(Variable::FILE_TYPE, 0);
        $loc->push_entity(Variable::CLASS_TYPE, 1);

        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(1, $loc->_class_interface_trait());

        $loc->pop_entity();
        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(null, $loc->_class_interface_trait());
    }

    public function test_in_entites_function_method() {
        $loc = $this->location("file.php", "");
        $loc->push_entity(Variable::FILE_TYPE, 0);
        $loc->push_entity(Variable::CLASS_TYPE, 1);
        $loc->push_entity(Variable::METHOD_TYPE, 2);

        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(1, $loc->_class_interface_trait());
        $this->assertEquals(2, $loc->_function_method());

        $loc->pop_entity();
        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(1, $loc->_class_interface_trait());
        $this->assertEquals(null, $loc->_function_method());
    }

    public function test_in_entites_function() {
        $loc = $this->location("file.php", "");
        $loc->push_entity(Variable::FILE_TYPE, 0);
        $loc->push_entity(Variable::FUNCTION_TYPE, 2);

        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(null, $loc->_class_interface_trait());
        $this->assertEquals(2, $loc->_function_method());

        $loc->pop_entity();
        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(null, $loc->_class_interface_trait());
        $this->assertEquals(null, $loc->_function_method());
    }

    public function test_in_entites_inline_function() {
        $loc = $this->location("file.php", "");
        $loc->push_entity(Variable::FILE_TYPE, 0);
        $loc->push_entity(Variable::CLASS_TYPE, 1);
        $loc->push_entity(Variable::METHOD_TYPE, 2);
        $loc->push_entity(Variable::FUNCTION_TYPE, 3);

        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(1, $loc->_class_interface_trait());
        $this->assertEquals(3, $loc->_function_method());

        $loc->pop_entity();
        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(1, $loc->_class_interface_trait());
        $this->assertEquals(2, $loc->_function_method());

        $loc->pop_entity();
        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(1, $loc->_class_interface_trait());
        $this->assertEquals(null, $loc->_function_method());
    }

    public function test_in_anonymous_class() {
        $loc = $this->location("file.php", "");
        $loc->push_entity(Variable::FILE_TYPE, 0);
        $loc->push_entity(Variable::CLASS_TYPE, 1);
        $loc->push_entity(Variable::CLASS_TYPE, 2);

        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(2, $loc->_class_interface_trait());

        $loc->pop_entity();
        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(1, $loc->_class_interface_trait());

        $loc->pop_entity();
        $this->assertEquals(0, $loc->_file());
        $this->assertEquals(null, $loc->_namespace());
        $this->assertEquals(null, $loc->_class_interface_trait());
        $this->assertEquals(null, $loc->_function_method());
    }

    public function test_current_node() {
        $loc = $this->location("file.php", "");
        $node = new Class_("foo");
        $loc->set_current_node($node);
        $this->assertEquals($node, $loc->current_node());         
    }

    public function test_line() {
        $loc = $this->location("file.php", "");
        $node = new Class_("foo", [], ["startLine" => 23]);
        $loc->set_current_node($node);
        $this->assertEquals(23, $loc->_line());
    }

    public function test_running_line_length() {
        $code = <<<CODE
12345
12

12345678
2
CODE;
        $loc = $this->location("file.php", $code);
        $this->assertEquals(null, $loc->get_running_line_length());

        $loc->_init_running_line_length();
        $expected = [0, 6, 9, 10, 19];
        $this->assertEquals($expected, $loc->get_running_line_length());
    }

    public function test_column() {
$code = <<<CODE
<?php

    class Foo {}
CODE;
        $loc = $this->location("file.php", $code);
        $node = new Class_("foo", [], ["startLine" => 3, "startFilePos" => 17]);
        $loc->set_current_node($node);
        $this->assertEquals(11, $loc->_column());
    }
}
