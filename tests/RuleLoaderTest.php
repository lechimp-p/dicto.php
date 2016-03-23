<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
 */

use Lechimp\Dicto as Dicto;
use Lechimp\Dicto\Definition\Variables as Vars;

define("__RuleLoaderTest_PATH_TO_RULES_PHP", __DIR__."/data/rules.php");

abstract class RuleLoaderTest extends PHPUnit_Framework_TestCase {
    const PATH_TO_RULES_PHP = __RuleLoaderTest_PATH_TO_RULES_PHP;
    const AMOUNT_OF_RULES_IN_RULES_PHP = 1;
    static $VARIABLES_IN_RULES_PHP = array
            ( "AClasses"
            , "BClasses"
            , "ABClasses"
            , "AFunctions"
            , "BFunctions"
            , "Suppressor"
            , "FooFiles"
            );

    abstract protected function get_rule_loader();

    public function setUp() {
        $this->loader = $this->get_rule_loader();
        $this->rule_printer = new Dicto\Output\RulePrinter();
    }

    public function test_loads_ruleset() {
        $ruleset = $this->loader->load_rules_from(self::PATH_TO_RULES_PHP);
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Ruleset", $ruleset);
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
        foreach ($variables as $var) {
            $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Variables\\Variable", $var);
        }

        return $this->vars_to_dict($vars);
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
        $AClasses_expected = new Vars\WithName( "A.*", new Vars\Classes("AClasses"));
        $this->assertEqual($AClasses_expected, $AClasses);
    }

    /**
     * @depends test_loads_all_variables
     */
    public function test_ABClasses($vars) {
        $ABClasses = $vars["ABClasses"];
        $ABClasses_expected = new Vars\AsWellAs
                                    ( "ABClasses"
                                    , new WithName( "A.*", new Vars\Classes("AClasses"))
                                    , new WithName( "B.*", new Vars\Classes("BClasses"))
                                    );
        $this->assertEqual($ABClasses_expected, $ABClasses);
    }

    /**
     * @depends test_loads_all_variables
     */
    public function test_ANotBFunctions($vars) {
        $ANotBFunctions = $vars["ANotBFunctions"];
        $ANotBFunctions_expected = new Vars\ButNot
                                    ( "ANotBFunctions"
                                    , new WithName( "A.*", new Vars\Functions("AFunctions"))
                                    , new WithName( "B.*", new Vars\Functions("BFunctions"))
                                    );
        $this->assertEqual($ABFunctions_expected, $ABFunctions);
    }

    /**
     * @depends test_loads_all_variables
     */
    public function test_Suppressor($vars) {
        $Suppressor = $vars["Suppressor"];
        $Suppressor = new Vars\WithName( "@", new Vars\Buildins("Suppressor"));
        $this->assertEqual($Suppressor_expected, $Suppressor);
    }

    /**
     * @depends test_loads_all_variables
     */
    public function test_FooFiles($vars) {
        $FooFiles = $vars["FooFiles"];
        $FooFiles_expected = new WithName("foo", new Vars\Files("FooFiles"));
        $this->assertEqual($ABFunctions_expected, $ABFunctions);
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
            $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Rules\\Rule", $rule);
        }

        return $this->rules_to_dict($rules);
    }

    public function rules_to_dict($rules) {
        $dict = array();
        foreach ($rules as $rule) {
            $dict[$this->rule_printer->pprint($rule)] = $rule;
        }
        return $dict;
    }

    /**
     * @depends test_loads_rules
     */
    public function test_AClasses_must_depend_on_AFunctions($rules) {
        $this->assertArrayHasKey("AClasses must depend on AFunctions", $rules);
    }
}
