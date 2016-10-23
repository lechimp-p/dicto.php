<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Indexer\LocationImpl;
use Lechimp\Dicto\Variables\Variable;
use PhpParser\Node\Stmt\Class_; 

class LocationTest extends PHPUnit_Framework_TestCase {
    public function location($file, $content) {
        return new LocationImpl($file, $content);
    }

    public function test_file_name() {
        $loc = $this->location("file.php", "");
        $this->assertEquals("file.php", $loc->file_name());
    }

    public function test_file_content() {
        $loc = $this->location("file.php", "some_content");
        $this->assertEquals("some_content", $loc->file_content());
    }

    public function test_in_entity_empty() {
        $loc = $this->location("file.php", "");
        $this->assertEquals([], $loc->in_entities());
    }

    public function test_in_entites() {
        $loc = $this->location("file.php", "");
        $loc->push_entity(Variable::FILE_TYPE, 0);
        $loc->push_entity(Variable::CLASS_TYPE, 1);

        $expected = 
            [ [Variable::FILE_TYPE, 0]
            , [Variable::CLASS_TYPE, 1]
            ];
        $this->assertEquals($expected, $loc->in_entities());
        $this->assertEquals(2, $loc->count_in_entity());

        $this->assertEquals([Variable::FILE_TYPE, 0], $loc->in_entity(0));
        $this->assertEquals([Variable::CLASS_TYPE, 1], $loc->in_entity(1));

        $loc->pop_entity();
        $this->assertEquals([[Variable::FILE_TYPE, 0]], $loc->in_entities());
        $this->assertEquals([Variable::FILE_TYPE, 0], $loc->in_entity(0));
        $this->assertEquals(1, $loc->count_in_entity());
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
        $this->assertEquals(23, $loc->line());
    }
}
