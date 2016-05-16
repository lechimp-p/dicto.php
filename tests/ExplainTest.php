<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

use Lechimp\Dicto as Dicto;
use Lechimp\Dicto\Rules;
use Lechimp\Dicto\Variables as Vars;

class ExplainTest extends PHPUnit_Framework_TestCase {
    /**
     * @dataProvider    explainable_provider
     */
    public function test_explain($explainable) {
        $this->assertEquals("", $explainable->explanation());
        $explained = $explainable->explain("EXPLANATION");
        $this->assertEquals(get_class($explainable), get_class($explained));
        $this->assertEquals("EXPLANATION", $explained->explanation());
    }

    public function explainable_provider() {
        $base = array
            ( new Vars\Classes("CLASSES")
            , new Vars\Functions("FUNCTIONS")
            , new Vars\Globals("GLOBALS")
            , new Vars\Files("FILES")
            , new Vars\Methods("METHODS")
            , new Vars\LanguageConstruct("LNG_CONSTRUCT", "@")
            , new Vars\Everything("EVERYTHING")
            );

        $explainable = array();
        foreach ($base as $b) {
            $explainable[] = array($b);
            $explainable[] = array(new Vars\WithName("the_name", $b));
            foreach ($base as $b2) {
                $explainable[] = array(new Vars\AsWellAs("AS_WELL_AS", $b, $b2));
                $explainable[] = array(new Vars\ButNot("BUT_NOT", $b, $b2));
            }
        } 

        $explainable[] = array
            ( new Rules\Rule
                ( Rules\Rule::MODE_CANNOT
                , new Vars\Classes("CLASSES")
                , new Rules\ContainText()
                , array("foo")
                )
            );
        $explainable[] = array
            ( new Rules\Rule
                ( Rules\Rule::MODE_ONLY_CAN
                , new Vars\Functions("FUNCTIONS")
                , new Rules\DependOn()
                , array(new Vars\Methods("METHODS"))
                )
            );
        $explainable[] = array
            ( new Rules\Rule
                ( Rules\Rule::MODE_ONLY_CAN
                , new Vars\Globals("GLOBALS")
                , new Rules\Invoke()
                , array(new Vars\LanguageConstruct("LNG_CONSTRUCT", "@"))
                )
            );

        return $explainable;
    }
}
