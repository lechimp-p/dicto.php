<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Variables as V;
use Lechimp\Dicto\Graph\IndexDB;

class VariableCompilationTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->db = new IndexDB();
    }

    public function test_compile_everything() {
        $var = new V\Everything();
        $compiled = $var->compile();

        $f = $this->db->_file("source.php", "A\nB");
        $c = $this->db->_class("AClass", $f, 1,2);
        $m = $this->db->_method("a_method", $c, $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$f],[$c],[$m]], $res);
    }

    public function test_compile_everything_negated() {
        $var = new V\Everything();
        $compiled = $var->compile(true);

        $f = $this->db->_file("source.php", "A\nB");
        $c = $this->db->_class("AClass", $f, 1,2);
        $m = $this->db->_method("a_method", $c, $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([], $res);
    }
}
