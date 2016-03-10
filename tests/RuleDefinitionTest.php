<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;
use Lechimp\Dicto\Definition as Def;

class RuleDefinitionTest extends PHPUnit_Framework_TestCase {
    public function test_variable_class() {
        $var = Dicto::_every()->_class();
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\_Variable", $var);
    } 

    public function test_variable_function() {
        $var = Dicto::_every()->_function();
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\_Variable", $var);
    }

    /**
     * @dataProvider same_variable_2tuple_provider 
     */
    public function test_variable_and(Def\_Variable $left, Def\_Variable $right) {
        $var = $left->_and($right);
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\_Variable", $var);
    }

    /**
     * @dataProvider same_variable_2tuple_provider 
     */
    public function test_variable_except(Def\_Variable $left, Def\_Variable $right) {
        $var = $left->_except($right);
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\_Variable", $var);
    }

    public function test_variable_buildin() {
        $var = Dicto::_every()->_buildin();
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\_Variable", $var);
    }

    public function test_variable_global() {
        $var = Dicto::_every()->_global();
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\_Variable", $var);
    }

    public function test_variable_file() {
        $var = Dicto::_every()->_file();
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\_Variable", $var);
    }

    /**
     * @dataProvider all_base_variables_provider 
     */
    public function test_variable_with_name(Def\_Variable $var) {
        $named = $var->_with()->_name("foo.*");
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\_Variable", $var);
    }

    public function test_and_only_works_on_same_type() {
        try {
            Dicto::_every()->_class()->_and(Dicto::_every()->_function());
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $_) {};
    }

    public function test_except_only_works_on_same_type() {
        try {
            Dicto::_every()->_class()->_except(Dicto::_every()->_function());
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $_) {};
    }

    public function same_variable_2tuple_provider() {
        $ls = $this->all_base_variables_provider();
        $rs = $this->all_base_variables_provider();
        $amount = count($ls);
        assert($amount == count($rs));
        $ret = array();
        for($i = 0; $i < $amount; $i++) {
            $l = $ls[$i];
            $r = $rs[$i];
            $ret[] = array($l[0], $r[0]);
        }
        return $ret;
    }

    public function all_base_variables_provider() {
        return array
            ( array(Dicto::_every()->_class())
            , array(Dicto::_every()->_function())
            , array(Dicto::_every()->_global())
            , array(Dicto::_every()->_file())
            , array(Dicto::_every()->_buildin())
            );
    }
}
