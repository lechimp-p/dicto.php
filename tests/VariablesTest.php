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
    /**
     * @dataProvider    var_test_cases_provider
     */
    public function test_name($var, $name, $_) {
        $this->assertEquals($name, $var->name());
    }

    /**
     * @dataProvider    var_test_cases_provider
     */
    public function test_withName($var, $_, $meaning) {
        $renamed = $var->withName("RENAMED"); 
        $this->assertEquals("RENAMED", $renamed->name());
        $this->assertEquals(get_class($var), get_class($renamed));
        $this->assertEquals($meaning, $renamed->meaning());
    }

    /**
     * @dataProvider    var_test_cases_provider
     */
    public function test_meaning($var, $_, $meaning) {
        $this->assertEquals($meaning, $var->meaning());
    }

    public function var_test_cases_provider() {
        return array
            ( array
                ( new V\Classes()
                , "Classes"
                , "classes"
                )
            , array
                ( new V\WithProperty
                    ( new V\Classes()
                    , new V\Name()
                    , array(".*GUI")
                    )
                , null
                , "classes with name: \".*GUI\""
                )
            , array
                ( new V\WithProperty
                    ( new V\Classes()
                    , new V\In()
                    , array(new V\Files)
                    )
                , null
                  // TODO: this is really inconsitent...
                , "classes in: Files" 
                )
            // TODO: add more TestCases here, that cover all
            //       types of variables.
            );
    }

    public function var_entities_provider() {
        return array
            ( array(new V\Classes())
            , array(new V\Interfaces())
            , array(new V\Functions())
            , array(new V\Globals())
            , array(new V\Files())
            , array(new V\Methods())
            // TODO: introduce LanguageConstruct here?
            );
    }
}
