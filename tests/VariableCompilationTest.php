<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Regexp;
use Lechimp\Dicto\Variables as V;
use Lechimp\Dicto\Graph\IndexDB;
use Lechimp\Dicto\Graph\PredicateFactory;

class VariableCompilationTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->db = new IndexDB();
        $this->f = new PredicateFactory();
    }

    public function test_compile_everything() {
        $var = new V\Everything();
        $compiled = $var->compile($this->f);

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
        $compiled = $var->compile($this->f);

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
        $compiled = $var->compile($this->f);

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

    public function test_compile_interfaces() {
        $var = new V\Interfaces();
        $compiled = $var->compile($this->f);

        $f = $this->db->_file("source.php", "A\nB");
        $i = $this->db->_interface("AnInterface", $f, 1,2);
        $m = $this->db->_method("a_method", $i, $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$i]], $res);
    }

    public function test_compile_traits() {
        $var = new V\Traits();
        $compiled = $var->compile($this->f);

        $f = $this->db->_file("source.php", "A\nB");
        $t = $this->db->_trait("ATrait", $f, 1,2);
        $m = $this->db->_method("a_method", $t, $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$t]], $res);
    }

    public function test_compile_methods() {
        $var = new V\Methods();
        $compiled = $var->compile($this->f);

        $f = $this->db->_file("source.php", "A\nB");
        $c = $this->db->_class("AClass", $f, 1,2);
        $m1 = $this->db->_method("a_method", $c, $f, 1, 2);
        $m2 = $this->db->_method_reference("another_method", $f, 1, 2);

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
        $compiled = $var->compile($this->f);

        $f = $this->db->_file("source.php", "A\nB");
        $c = $this->db->_class("AClass", $f, 1,2);
        $f1 = $this->db->_function("a_function", $f, 1, 2);
        $f2 = $this->db->_function_reference("another_function", $f, 1, 2);

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
        $compiled = $var->compile($this->f);

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
        $var = new V\Die_();
        $compiled = $var->compile($this->f);

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
        $compiled = $var->compile($this->f);

        $f = $this->db->_file("source.php", "A\nB");
        $c = $this->db->_class("AClass", $f, 1,2);
        $m1 = $this->db->_method("a_method", $c, $f, 1, 2);
        $m2 = $this->db->_method_reference("another_method", $f, 1, 2);
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
            , array(new Regexp("AClass"))
            );
        $var = new V\Except(new V\Classes, $var);
        $compiled = $var->compile($this->f);

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
            , array(new Regexp("AClass"))
            );
        $compiled = $var->compile($this->f);

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
            , array(new Regexp(".Class"))
            );
        $compiled = $var->compile($this->f);

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
            , array(new Regexp("AClass"))
            );
        $var = new V\WithProperty
            ( new V\Methods()
            , new V\In()
            , array($a_classes)
            );
        $compiled = $var->compile($this->f);

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

    public function test_compile_methods_in_some_interface() {
        $a_interfaces = new V\WithProperty
            ( new V\Interfaces()
            , new V\Name()
            , array(new Regexp("AInterface"))
            );
        $var = new V\WithProperty
            ( new V\Methods()
            , new V\In()
            , array($a_interfaces)
            );
        $compiled = $var->compile($this->f);

        $f = $this->db->_file("source.php", "A\nB");
        $i1 = $this->db->_interface("AInterface", $f, 1,2);
        $m1 = $this->db->_method("a_method", $i1, $f, 1, 2);
        $i2 = $this->db->_interface("BInterface", $f, 1,2);
        $m2 = $this->db->_method("a_method", $i2, $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$m1]], $res);
    }

    public function test_compile_methods_in_some_traits() {
        $a_traits = new V\WithProperty
            ( new V\Traits()
            , new V\Name()
            , array(new Regexp("ATrait"))
            );
        $var = new V\WithProperty
            ( new V\Methods()
            , new V\In()
            , array($a_traits)
            );
        $compiled = $var->compile($this->f);

        $f = $this->db->_file("source.php", "A\nB");
        $t1 = $this->db->_trait("ATrait", $f, 1,2);
        $m1 = $this->db->_method("a_method", $t1, $f, 1, 2);
        $t2 = $this->db->_trait("BTrait", $f, 1,2);
        $m2 = $this->db->_method("a_method", $t2, $f, 1, 2);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$m1]], $res);
    }

    public function test_compile_anything_in_some_namespace() {
        $var = new V\WithProperty
            ( new V\Everything()
            , new V\In()
            , array(new V\Namespaces())
            );
        $compiled = $var->compile($this->f);

        $f = $this->db->_file("source.php", "A\nB");
        $n = $this->db->_namespace("SomeNamespace");
        $c1 = $this->db->_class("AClass", $f, 1,2);
        $c2 = $this->db->_class("AClass", $f, 1,2, $n);
        $i1 = $this->db->_interface("AnInterface", $f, 1,2);
        $i2 = $this->db->_interface("AnInterface", $f, 1,2, $n);
        $t1 = $this->db->_trait("ATrait", $f, 1,2);
        $t2 = $this->db->_trait("ATrait", $f, 1,2, $n);
        $f1 = $this->db->_function("a_function", $f, 1,2);
        $f2 = $this->db->_function("a_function", $f, 1,2, $n);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$c2], [$i2], [$t2], [$f2]], $res);
    }

    public function test_compile_anything_in_specific_nested_namespace() {
        $var = new V\WithProperty
            ( new V\Everything()
            , new V\In()
            , array(new V\WithProperty
                ( new V\Namespaces()
                , new V\Name()
                , array(new Regexp("Test[\\\\]Namespace"))
                ))
            );
        $compiled = $var->compile($this->f);

        $f = $this->db->_file("source.php", "A\nB");
        $n = $this->db->_namespace("Test\\Namespace");
        $c1 = $this->db->_class("AClass", $f, 1,2);
        $c2 = $this->db->_class("AClass", $f, 1,2, $n);
        $i1 = $this->db->_interface("AnInterface", $f, 1,2);
        $i2 = $this->db->_interface("AnInterface", $f, 1,2, $n);
        $t1 = $this->db->_trait("ATrait", $f, 1,2);
        $t2 = $this->db->_trait("ATrait", $f, 1,2, $n);
        $f1 = $this->db->_function("a_function", $f, 1,2);
        $f2 = $this->db->_function("a_function", $f, 1,2, $n);

        $res = $this->db->query()
            ->filter($compiled)
            ->extract(function($n,&$r) {
                $r[] = $n;
            })
            ->run([]);
        $this->assertEquals([[$c2], [$i2], [$t2], [$f2]], $res);
    }
}
