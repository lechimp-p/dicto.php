<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Regexp;
use Lechimp\Dicto\Rules;
use Lechimp\Dicto\Variables as Vars;

class ExplainTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider    explainable_provider
     */
    public function test_explain($explainable)
    {
        $this->assertEquals("", $explainable->explanation());
        $explained = $explainable->withExplanation("EXPLANATION");
        $this->assertEquals(get_class($explainable), get_class($explained));
        $this->assertEquals("EXPLANATION", $explained->explanation());
        $methods = get_class_methods(get_class($explainable));
        if ($explainable instanceof Vars\Variable) {
            foreach ($methods as $m) {
                if ($m == "__construct"
                || $m == "explanation"
                || $m == "withName"
                || $m == "is_type"
                || $m == "withExplanation"
                || $m == "compile") {
                    continue;
                }
                $this->assertEquals(
                    $explainable->$m(),
                    $explained->$m(),
                    "property '$m' should match"
                );
            }
        }
        if ($explained instanceof Rules\Rule) {
            $this->assertEquals($explainable->mode(), $explained->mode());
            $this->assertEquals($explainable->subject(), $explained->subject());
            $this->assertEquals($explainable->schema(), $explained->schema());
            $this->assertEquals($explainable->arguments(), $explained->arguments());
        }
    }

    public function explainable_provider()
    {
        $base = array( new Vars\Classes("CLASSES")
            , new Vars\Interfaces("INTERFACES")
            , new Vars\Functions("FUNCTIONS")
            , new Vars\Globals("GLOBALS")
            , new Vars\Files("FILES")
            , new Vars\Methods("METHODS")
            , new Vars\ExitOrDie("EXITORDIE")
            , new Vars\ErrorSuppressor("ERROR_SUPPRESSOR")
            , new Vars\Everything("EVERYTHING")
            );

        $explainable = array();
        foreach ($base as $b) {
            $explainable[] = array($b);
            $explainable[] = array( new Vars\WithProperty(
                        $b,
                        new Vars\Name(),
                        array(new Regexp("the_name"))
                    )
                );
            foreach ($base as $b2) {
                $explainable[] = array((new Vars\Any(array($b, $b2)))
                                        ->withName("AS_WELL_AS"));
                $explainable[] = array((new Vars\Except($b, $b2))
                                        ->withName("BUT_NOT"));
            }
        }

        $explainable[] = array( new Rules\Rule(
                    Rules\Rule::MODE_CANNOT,
                    new Vars\Classes("CLASSES"),
                    new Rules\ContainText(),
                    array(new Regexp("foo"))
                )
            );
        $explainable[] = array( new Rules\Rule(
                    Rules\Rule::MODE_ONLY_CAN,
                    new Vars\Functions("FUNCTIONS"),
                    new Rules\DependOn(),
                    array(new Vars\Methods("METHODS"))
                )
            );
        $explainable[] = array( new Rules\Rule(
                    Rules\Rule::MODE_ONLY_CAN,
                    new Vars\Globals("GLOBALS"),
                    new Rules\Invoke(),
                    array(new Vars\ErrorSuppressor("ERROR_SUPPRESSOR"))
                )
            );

        return $explainable;
    }
}
