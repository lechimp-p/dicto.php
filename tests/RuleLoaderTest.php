<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto as Dicto;
use Lechimp\Dicto\Variables as Vars;
use Lechimp\Dicto\Rules as Rules;

class RuleLoaderTest extends PHPUnit_Framework_TestCase {
    static $PATH_TO_RULES_PHP = null;
    const AMOUNT_OF_RULES_IN_RULES_PHP = 1;
    static $VARIABLES_IN_RULES_PHP = array
            ( "AClasses"
            , "BClasses"
            , "ABClasses"
            , "AFunctions"
            , "BFunctions"
            , "ANotBFunctions"
            , "Suppressor"
            , "FooFiles"
            );

    public function setUp() {
        self::$PATH_TO_RULES_PHP = __DIR__."/data/rules.php";
        $this->loader = new Dicto\App\RuleLoader();

        $this->AClasses =
            new Vars\WithName( "A.*", new Vars\Classes("AClasses"));
        $this->ABClasses =
            (new Vars\AsWellAs
                ( new Vars\WithName( "A.*", new Vars\Classes("AClasses"))
                , new Vars\WithName( "B.*", new Vars\Classes("BClasses"))
                )
            )->withName("ABClasses");
        $this->AFunctions =
            new Vars\WithName( "a_.*", new Vars\Functions("AFunctions"));
        $this->ANotBFunctions =
            (new Vars\ButNot
                ( $this->AFunctions
                , new Vars\WithName( "b_.*", new Vars\Functions("BFunctions"))
                )
             )->withName("ANotBFunctions");
        $this->Suppressor =
            new Vars\LanguageConstruct("Suppressor", "@");
        $this->FooFiles =
            new Vars\WithName("foo", new Vars\Files("FooFiles"));
    }

    public function test_loads_ruleset() {
        list($ruleset, $_) = $this->loader->load_rules_from(self::$PATH_TO_RULES_PHP);
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Rules\\Ruleset", $ruleset);
        return $ruleset;
    }

    public function test_throws_on_invalid_file() {
        try {
            $this->loader->load_rules_from("fooooo.py");
            $this->assertFalse("Should have thrown.");
        }
        catch (\InvalidArgumentException $e) {}
    }

    // VARIABLES

    /**
     * @depends test_loads_ruleset
     */
    public function test_loads_variables($ruleset) {
        $vars = $ruleset->variables();
        $this->assertInternalType("array", $vars);
        $this->assertCount(count(self::$VARIABLES_IN_RULES_PHP), $vars);
        foreach ($vars as $var) {
            $this->assertInstanceOf("\\Lechimp\\Dicto\\Variables\\Variable", $var);
        }

        return $this->vars_to_dict($vars);
    }

    public function test_loads_variables_twice() {
        list($ruleset, $_) = $this->loader->load_rules_from(self::$PATH_TO_RULES_PHP);
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Rules\\Ruleset", $ruleset);
        $vars = $ruleset->variables();
        $this->assertInternalType("array", $vars);
        $this->assertCount(count(self::$VARIABLES_IN_RULES_PHP), $vars);
    }

    public function vars_to_dict($vars) {
        $dict = array();
        foreach ($vars as $var) {
            $dict[$var->name()] = $var;
        }
        return $dict;
    }

    /**
     * @depends test_loads_variables
     */
    public function test_loads_all_variables($vars) {
        foreach (self::$VARIABLES_IN_RULES_PHP as $var_name) {
            $this->assertArrayHasKey($var_name, $vars);
        }
        return $vars;
    }

    /**
     * @depends test_loads_all_variables
     */
    public function test_AClasses($vars) {
        $AClasses = $vars["AClasses"];
        $this->assertEquals($this->AClasses, $AClasses);
    }

    /**
     * @depends test_loads_all_variables
     */
    public function test_ABClasses($vars) {
        $ABClasses = $vars["ABClasses"];
        $this->assertEquals($this->ABClasses, $ABClasses);
    }

    /**
     * @depends test_loads_all_variables
     */
    public function test_ANotBFunctions($vars) {
        $ANotBFunctions = $vars["ANotBFunctions"];
        $this->assertEquals($this->ANotBFunctions, $ANotBFunctions);
    }

    /**
     * @depends test_loads_all_variables
     */
    public function test_Suppressor($vars) {
        $Suppressor = $vars["Suppressor"];
        $this->assertEquals($this->Suppressor, $Suppressor);
    }

    /**
     * @depends test_loads_all_variables
     */
    public function test_FooFiles($vars) {
        $FooFiles = $vars["FooFiles"];
        $this->assertEquals($this->FooFiles, $FooFiles);
    }

    // RULES

    /**
     * @depends test_loads_ruleset
     */
    public function test_loads_rules($ruleset) {
        $rules = $ruleset->rules();
        $this->assertInternalType("array", $rules);
        $this->assertCount(self::AMOUNT_OF_RULES_IN_RULES_PHP, $rules);
        foreach ($rules as $rule) {
            $this->assertInstanceOf("\\Lechimp\\Dicto\\Rules\\Rule", $rule);
        }

        return $this->rules_to_dict($rules);
    }

    public function rules_to_dict($rules) {
        $dict = array();
        foreach ($rules as $rule) {
            $dict[$rule->pprint()] = $rule;
        }
        return $dict;
    }

    /**
     * @depends test_loads_rules
     */
    public function test_AClasses_must_depend_on_AFunctions($rules) {
        $pp = "AClasses must invoke AFunctions";
        $this->assertArrayHasKey($pp, $rules);

        $expected = new Rules\Rule
            ( Rules\Rule::MODE_MUST
            , $this->AClasses
            , new Rules\Invoke()
            , array($this->AFunctions)
            );
        $this->assertEquals($expected, $rules[$pp]);
    }
}
