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

define("__RuleLoaderTest_PATH_TO_RULES_PHP", __DIR__."/data/rules.php");

abstract class RuleLoaderTest extends PHPUnit_Framework_TestCase {
    const PATH_TO_RULES_PHP = __RuleLoaderTest_PATH_TO_RULES_PHP;
    const AMOUNT_OF_RULES_IN_RULES_PHP = 1;

    abstract protected function get_rule_loader();

    public function setUp() {
        $this->loader = $this->get_rule_loader();
        $this->rule_printer = new Dicto\Output\RulePrinter();
    }

    public function test_loads_rules() {
        $rules = $this->loader->load_rules_from(self::PATH_TO_RULES_PHP);
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
    }

    /**
     * @depends test_loads_rules
     */
    public function test_AClasses_must_depend_on_AFunctions($rules) {
        $this->assertArrayHasKey("AClasses must depend on AFunctions", $rules);
    }
}
