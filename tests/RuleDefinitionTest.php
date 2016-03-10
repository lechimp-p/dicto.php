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
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Variable", $var);
    } 

    public function test_variable_function() {
        $var = Dicto::_every()->_function();
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\Variable", $var);
    }

    /**
     * @dataProvider variables_with_and_provider
     */
    public function test_variable_and(Def\Variable $left, Def\Variable $right) {
        $var = $left->_and($right);
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Variable", $var);
    }

    /**
     * @dataProvider variables_with_except_provider
     */
    public function test_variable_except(Def\Variable $left, Def\Variable $right) {
        $var = $left->_except($right);
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Variable", $var);
    }

    public function test_variable_buildin() {
        $var = Dicto::_every()->_buildin();
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Variable", $var);
    }

    public function test_variable_global() {
        $var = Dicto::_every()->_global();
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Variable", $var);
    }

    public function test_variable_file() {
        $var = Dicto::_every()->_file();
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Variable", $var);
    }

    /**
     * @dataProvider variables_with_name_provider
     */
    public function test_variable_with_name(Def\Variable $var) {
        $named = $var->_with()->_name("foo.*");
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Variable", $var);
    }

    public function test_and_only_works_on_same_type() {
    }

    public function test_except_only_works_on_same_type() {
    }

    public function variables_with_and_provider() {
        return array
            ( array(Dicto::_every()->_class(), Dicto::_every()->_class())
            , array(Dicto::_every()->_function(), Dicto::_every()->_function())
            , array(Dicto::_every()->_global(), Dicto::_every()->_global())
            , array(Dicto::_every()->_buildin(), Dicto::_every()->_buildin())
            , array(Dicto::_every()->_file(), Dicto::_every()->_file())
            );
    }

    public function variables_with_except_provider() {
        return array
            ( array(Dicto::_every()->_class(), Dicto::_every()->_class())
            , array(Dicto::_every()->_function(), Dicto::_every()->_function())
            , array(Dicto::_every()->_global(), Dicto::_every()->_global())
            , array(Dicto::_every()->_buildin(), Dicto::_every()->_buildin())
            , array(Dicto::_every()->_file(), Dicto::_every()->_file())
            );
    }

    public function variables_with_name_provider() {
        return array
            ( array(Dicto::_every()->_class())
            , array(Dicto::_every()->_function())
            , array(Dicto::_every()->_global())
            , array(Dicto::_every()->_file())
            , array(Dicto::_every()->_buildin())
            );
    }
}
