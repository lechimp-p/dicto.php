<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Graph\Entity;

class TestEntity extends Entity {
}

class GraphEntityTest extends PHPUnit_Framework_TestCase {
    public function test_type() {
        $e = new TestEntity("a_type", array());

        $this->assertEquals("a_type", $e->type());
    }

    public function test_properties() {
        $e = new TestEntity("a_type", ["prop" => "value"]);

        $this->assertEquals(["prop" => "value"], $e->properties());
    }

    public function test_property() {
        $e = new TestEntity("a_type", ["prop" => "value"]);

        $this->assertEquals("value", $e->property("prop"));
        try {
            $e->property("another_prop");
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {}
    }

    public function test_has_property() {
        $e = new TestEntity("a_type", ["prop" => "value"]);

        $this->assertTrue($e->has_property("prop"));
        $this->assertFalse($e->has_property("another_prop"));
    }
}
