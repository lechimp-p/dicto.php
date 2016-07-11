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

class VariablesTest extends PHPUnit_Framework_TestCase {
    // TODO: add a test on naming here.

    /**
     * @dataProvider    var_test_cases_provider
     */
    public function test_meaning($var, $_, $meaning) {
        $this->assertEquals($meaning, $var->meaning());
    }

    public function var_test_cases_provider() {
        return array
            ( array
                ( new V\Classes("allClasses")
                , "allClasses"
                , "classes"
                )
            , array
                ( new V\WithName
                    ( ".*GUI"
                    , new V\Classes("GUIClasses")
                    ) 
                , "GUIClasses"
                , "classes with name \".*GUI\""
                )
            // TODO: add more TestCases here, that cover all
            //       types of variables.
            );
    }
}
