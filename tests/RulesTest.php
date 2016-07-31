<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Rules as Rules;
use Lechimp\Dicto\Variables as Vars;

class RulesTest extends PHPUnit_Framework_TestCase {
    public function test_checked_on() {
        $rule =
            new Rules\Rule
                ( Rules\Rule::MODE_MUST
                , new Vars\Classes("CLASSES")
                , new Rules\DependOn()
                , array(new Vars\Functions("FUNCTIONS"))
                );
        $expected_checked_on =
            new Vars\Classes("CLASSES");
        $this->assertEquals($expected_checked_on, $rule->checked_on());
    }

    public function test_checked_on_inversion_on_only_can() {
        $rule =
            new Rules\Rule
                ( Rules\Rule::MODE_ONLY_CAN
                , new Vars\Classes("CLASSES")
                , new Rules\Invoke()
                , array(new Vars\Functions("FUNCTIONS"))
                );
        $expected_checked_on =
            new Vars\Except
                ( new Vars\Everything("EVERYTHING")
                , new Vars\Classes("CLASSES")
                );
        $this->assertEquals($expected_checked_on, $rule->checked_on());
    }

    public function test_variables_of_depend_on() {
        $rule =
            new Rules\Rule
                ( Rules\Rule::MODE_MUST
                , new Vars\Classes("CLASSES")
                , new Rules\DependOn()
                , array(new Vars\Functions("FUNCTIONS"))
                );
        $expected = array(new Vars\Classes("CLASSES"), new Vars\Functions("FUNCTIONS"));
        $this->assertEquals($expected, $rule->variables());
    }

    public function test_variables_of_contain_text() {
        $rule =
            new Rules\Rule
                ( Rules\Rule::MODE_MUST
                , new Vars\Classes("CLASSES")
                , new Rules\ContainText()
                , array("foo")
                );
        $expected = array(new Vars\Classes("CLASSES"));
        $this->assertEquals($expected, $rule->variables());
    }

    public function test_variables_of_invoke() {
        $rule =
            new Rules\Rule
                ( Rules\Rule::MODE_MUST
                , new Vars\Classes("CLASSES")
                , new Rules\Invoke()
                , array(new Vars\Functions("FUNCTIONS"))
                );
        $expected = array(new Vars\Classes("CLASSES"), new Vars\Functions("FUNCTIONS"));
        $this->assertEquals($expected, $rule->variables());
    }
}
