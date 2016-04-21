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
use Lechimp\Dicto\Definition as Def;

define("__AnalyzerTest_PATH_TO_RULES_PHP", __DIR__."/data/rules.php");
define("__AnalyzerTest_PATH_TO_SRC", __DIR__."/data/src");

abstract class AnalyzerTest extends PHPUnit_Framework_TestCase {
    const PATH_TO_RULES_PHP = __AnalyzerTest_PATH_TO_RULES_PHP;
    const PATH_TO_SRC = __AnalyzerTest_PATH_TO_SRC;

    abstract protected function get_analyzer(Def\Ruleset $ruleset);

    public function setUp() {
        $this->pprinter = new Dicto\Output\RulePrinter;
        $loader = new Dicto\App\Implementation\RuleLoader();
        $this->ruleset = $loader->load_rules_from(self::PATH_TO_RULES_PHP);
        $this->analyzer = $this->get_analyzer($this->ruleset);
    }

    public function test_is_analyzer() {
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Analysis\\Analyzer", $this->analyzer);
        return $this->analyzer;
    }

    /**
     * @depends test_is_analyzer
     */   
    public function test_has_results($analyzer) {
        $result = $analyzer->run_analysis_on(self::PATH_TO_SRC);

        $this->assertInstanceOf("\\Lechimp\\Dicto\\Analysis\\Result", $result);

        return $result;
    } 

    protected function get_rule($pretty_rule, Def\Ruleset $ruleset) {
        $rules = $ruleset->rules();
        foreach ($rules as $rule) {
            if ($this->pprinter->pprint($rule) == $pretty_rule) {
                return $rule;
            }
        }
        $this->assertFalse("Could not get rule $pretty_rule");
    }

    /**
     * @depends test_has_results
     */
    public function test_AClasses_must_invoke_AFunctions($result) {
        $rule = $this->get_rule("AClasses must invoke AFunctions", $result->ruleset());
        $violations = $result->violations_of($rule);
        $violations_A2 = $result->violations_in(__DIR__."/data/src/A2.php");
        $violations_B1 = $result->violations_in(__DIR__."/data/src/B2.php");

        $this->assertCount(1, $violations);
        $violation = $violations[0];

        $this->assertEquals(__DIR__."/data/src/A2.php", $violation->filename());
        $this->assertEquals($rule, $violation->rule());
        $this->assertEquals("class A2 {", $violation->line());
        $this->assertEquals(11, $violation->line_no());

        $this->assertContains($violation, $violations_A2);
        $this->assertNotContains($violation, $violations_B1);
    } 
}
