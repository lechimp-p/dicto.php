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

    public function test_compile_files() {
        $var = new V\Files();
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
        $this->assertEquals([[$f]], $res);
    }

    public function test_compile_classes() {
        $var = new V\Classes();
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
        $this->assertEquals([[$c]], $res);
    }

    public function test_compile_methods() {
        $var = new V\Methods();
        $compiled = $var->compile();

        $f = $this->db->_file("source.php", "A\nB");
        $c = $this->db->_class("AClass", $f, 1,2);
        $m1 = $this->db->_method("a_method", $c, $f, 1, 2);
        $m2 = $this->db->_method_reference("another_method", $f, 1);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$m1],[$m2]], $res);
    }

    public function test_compile_functions() {
        $var = new V\Functions();
        $compiled = $var->compile();

        $f = $this->db->_file("source.php", "A\nB");
        $c = $this->db->_class("AClass", $f, 1,2);
        $f1 = $this->db->_function("a_function", $f, 1, 2);
        $f2 = $this->db->_function_reference("another_function", $f, 1);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$f1],[$f2]], $res);
    }

    public function test_compile_globals() {
        $var = new V\Globals();
        $compiled = $var->compile();

        $f = $this->db->_file("source.php", "A\nB");
        $c = $this->db->_class("AClass", $f, 1,2);
        $g = $this->db->_global("a_global", $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$g]], $res);
    }

    public function test_compile_language_constructs() {
        $var = new V\LanguageConstruct("die");
        $compiled = $var->compile();

        $f = $this->db->_file("source.php", "A\nB");
        $c = $this->db->_class("AClass", $f, 1,2);
        $l = $this->db->_language_construct("die", $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$l]], $res);
    }

    public function test_compile_any() {
        $var = new V\Any([new V\Classes, new V\Methods()]);
        $compiled = $var->compile();

        $f = $this->db->_file("source.php", "A\nB");
        $c = $this->db->_class("AClass", $f, 1,2);
        $m1 = $this->db->_method("a_method", $c, $f, 1, 2);
        $m2 = $this->db->_method_reference("another_method", $f, 1);
        $g = $this->db->_global("a_global", $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$c],[$m1],[$m2]], $res);
    }

    public function test_compile_except() {
        $var = new V\WithProperty
            ( new V\Classes()
            , new V\Name()
            , array("AClass")
            );
        $var = new V\Except(new V\Classes, $var);
        $compiled = $var->compile();

        $f = $this->db->_file("source.php", "A\nB");
        $c1 = $this->db->_class("AClass", $f, 1, 2);
        $c2 = $this->db->_class("BClass", $f, 1, 2);
        $g = $this->db->_global("a_global", $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$c2]], $res);
    }

    public function test_compile_name1() {
        $var = new V\WithProperty
            ( new V\Classes()
            , new V\Name()
            , array("AClass")
            );
        $compiled = $var->compile();

        $f = $this->db->_file("source.php", "A\nB");
        $c1 = $this->db->_class("AClass", $f, 1, 2);
        $c2 = $this->db->_class("BClass", $f, 1, 2);
        $m = $this->db->_method("a_method", $c1, $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$c1]], $res);
    }

    public function test_compile_name2() {
        $var = new V\WithProperty
            ( new V\Classes()
            , new V\Name()
            , array(".Class")
            );
        $compiled = $var->compile();

        $f = $this->db->_file("source.php", "A\nB");
        $c1 = $this->db->_class("AClass", $f, 1, 2);
        $c2 = $this->db->_class("BClass", $f, 1, 2);
        $m = $this->db->_method("a_method", $c1, $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$c1],[$c2]], $res);
    }

    public function test_compile_methods_in_some_classes() {
        $a_classes = new V\WithProperty
            ( new V\Classes()
            , new V\Name()
            , array("AClass")
            );
        $var = new V\WithProperty
            ( new V\Methods()
            , new V\In()
            , array($a_classes)
            );
        $compiled = $var->compile();

        $f = $this->db->_file("source.php", "A\nB");
        $c1 = $this->db->_class("AClass", $f, 1,2);
        $m1 = $this->db->_method("a_method", $c1, $f, 1, 2);
        $c2 = $this->db->_class("BClass", $f, 1,2);
        $m2 = $this->db->_method("a_method", $c2, $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$m1]], $res);
    }
}
