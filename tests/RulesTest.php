<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Definition\Rules as Rules;
use Lechimp\Dicto\Rules as R;
use Lechimp\Dicto\Definition\Variables as Vars;

class RulesTest extends PHPUnit_Framework_TestCase {
    public function test_checked_on() {
        $rule =
            new Rules\Relation
                ( Rules\Rule::MODE_MUST
                , new Vars\Classes("CLASSES")
                , new Vars\Functions("FUNCTIONS")
                , new R\DependOn
                );
        $expected_checked_on =
            new Vars\Classes("CLASSES");
        $this->assertEquals($expected_checked_on, $rule->checked_on());
    }

    public function test_checked_on_inversion_on_only_can() {
        $rule =
            new Rules\Relation
                ( Rules\Rule::MODE_ONLY_CAN
                , new Vars\Classes("CLASSES")
                , new Vars\Functions("FUNCTIONS")
                , new R\Invoke
                );
        $expected_checked_on =
            new Vars\ButNot
                ( "ONLY_CAN_INVERSION"
                , new Vars\Everything("EVERYTHING")
                , new Vars\Classes("CLASSES")
                );
        $this->assertEquals($expected_checked_on, $rule->checked_on());
    }

    public function test_variables_of_depend_on() {
        $rule =
            new Rules\Relation
                ( Rules\Rule::MODE_MUST
                , new Vars\Classes("CLASSES")
                , new Vars\Functions("FUNCTIONS")
                , new R\DependOn
                );
        $expected = array(new Vars\Classes("CLASSES"), new Vars\Functions("FUNCTIONS"));
        $this->assertEquals($expected, $rule->variables());
    }

    public function test_variables_of_contain_text() {
        $rule =
            new Rules\Property
                ( Rules\Rule::MODE_MUST
                , new Vars\Classes("CLASSES")
                , new R\ContainText()
                , array("foo")
                );
        $expected = array(new Vars\Classes("CLASSES"));
        $this->assertEquals($expected, $rule->variables());
    }

    public function test_variables_of_invoke() {
        $rule =
            new Rules\Relation
                ( Rules\Rule::MODE_MUST
                , new Vars\Classes("CLASSES")
                , new Vars\Functions("FUNCTIONS")
                , new R\Invoke
                );
        $expected = array(new Vars\Classes("CLASSES"), new Vars\Functions("FUNCTIONS"));
        $this->assertEquals($expected, $rule->variables());
    }
}
