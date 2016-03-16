<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;
use Lechimp\Dicto\Definition as Def;

class RuleDefinitionTest extends PHPUnit_Framework_TestCase {
    public function test_ruleset() {
        Dicto::startDefinition();
        $defs = Dicto::endDefinition();
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Ruleset", $defs);
        $this->assertEquals(array(), $defs->variables());
        $this->assertEquals(array(), $defs->rules());
    }

    public function check_var_definitions($names, $definition) {
        Dicto::startDefinition();
        $definition();
        $defs = Dicto::endDefinition();
        $variables = $defs->variables();

        $this->assertEquals(count($names), count($variables));
        foreach ($names as $name) {
            $this->assertArrayHasKey($name, $variables);
            $var = $variables[$name];
            $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Variable", $var);
        }
    }

    protected function check_single_var_definition($name, $definition) {
        $def = function() use ($name, $definition) { $definition($name); };
        $this->check_var_definitions(array($name), $def);
    }

    /**
     * @dataProvider all_base_variables_provider
     */
    public function test_base_variable($name, $definition) {
        $this->check_single_var_definition($name, $definition);
    } 

    /**
     * @dataProvider same_base_variable_2tuple_provider 
     */
    public function test_variable_and($l, $r, $def) {
        $this->check_var_definitions(array($l, $r, "both"), function() use ($l, $r, $def) {
            $def();
            Dicto::both()->means()->$l()->as_well_as()->$r();
        });
    }

    /**
     * @dataProvider same_base_variable_2tuple_provider 
     */
    public function test_variable_except($l, $r, $def) {
        $this->check_var_definitions(array($l, $r, "one"), function() use ($l, $r, $def) {
            $def();
            Dicto::one()->means()->$l()->but_not()->$r();
        });
    }

    /**
     * @dataProvider all_base_variables_provider 
     */
    public function test_variable_with_name($name, $def) {
        $this->check_single_var_definition($name, function ($n) use ($def) {
            $var = $def($n);
            $var->with()->name("foo.*");
        });
    }

    /**
     * @dataProvider different_base_variable_2tuple_provider
     */
    public function test_and_only_works_on_same_type($l, $r, $def) {
        try {
            $this->check_single_var_definition("__IRRELEVANT__", function($_) use ($l, $r, $def) {
                $def();
                Dicto::wont_happen()->means()->$l()->as_well_as()->$r();
            });
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $_) {};
    }

    /**
     * @dataProvider different_base_variable_2tuple_provider 
     */
    public function test_except_only_works_on_same_type($l, $r, $def) {
        try {
            $this->check_single_var_definition("__IRRELEVANT__", function($_) use ($l, $r, $def) {
                $def();
                Dicto::wont_happen()->means()->$l()->but_not()->$r();
            });
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $_) {};
    }


    /**
     * @dataProvider all_base_variables_provider
     */
    public function test_explain_variables($name, $def) {
        $this->check_single_var_definition($name, function ($n) use ($def) {
            $var = $def($n);
            $var->explain("EXPLANATION");
        });
    }

    public function check_rule($definition) {
        Dicto::startDefinition();
        $definition();
        $defs = Dicto::endDefinition();
        $rules = $defs->rules();

        $this->assertCount(1, $rules);
        $this->assertInstanceOf("\\Lechimp\\Dicto\\Definition\\Rule", $rules[0]);
        return $rules[0]; 
    }

    /**
     * @dataProvider some_rules_provider
     */
    public function test_rule($def) {
        $this->check_rule($def);
    }

    public function test_with_name_flawed_regexp() {
        try {
            $this->check_rule(function() {
                Dicto::flawed()->means()->classes()->with()->name("(");
            });
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $_) {}
    }

    public function test_contains_text_flawed_regexp() {
        try {
            $this->check_rule(function() {
                Dicto::allFiles()->means()->files();
                Dicto::allFiles()->cannot()->contain_text("(");
            });
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $_) {}
    }

    public function all_base_variables_provider() {
        return array
            ( array("allClasses", function($n){return Dicto::$n()->means()->classes();})
            , array("allFunctions", function($n){return Dicto::$n()->means()->functions();})
            , array("allBuildins", function($n){return Dicto::$n()->means()->buildins();})
            , array("allGlobals", function($n){return Dicto::$n()->means()->globals();})
            , array("allFiles", function($n){return Dicto::$n()->means()->files();})
            );
    }

    public function same_base_variable_2tuple_provider() {
        $ls = $this->all_base_variables_provider();
        $rs = $this->all_base_variables_provider();
        $amount = count($ls);
        assert($amount == count($rs));
        $ret = array();
        for($i = 0; $i < $amount; $i++) {
            list($ln, $lf) = array($ls[$i][0]."1", $ls[$i][1]);
            list($rn, $rf) = array($rs[$i][0]."2", $rs[$i][1]);
            $def = function() use ($ln, $lf, $rn, $rf) { $lf($ln); $rf($rn); };
            $ret[] = array($ln, $rn, $def); 
        }
        return $ret;
    }

    public function different_base_variable_2tuple_provider() {
        $ls = $this->all_base_variables_provider();
        $rs = $this->all_base_variables_provider();
        $ret = array();
        foreach ($ls as $l) {
            foreach ($rs as $r) {
                if ($l[0] === $r[0]) {
                    continue;
                }
                list($ln, $lf) = array($l[0]."1", $l[1]);
                list($rn, $rf) = array($r[0]."2", $r[1]);
                $def = function() use ($ln, $lf, $rn, $rf) { $lf($ln); $rf($rn); };
                $ret[] = array($ln, $rn, $def); 
            }
        }
        return $ret;
    }

    public function base_variable_2tuple_provider() {
        $ls = $this->all_base_variables_provider();
        $rs = $this->all_base_variables_provider();
        $ret = array();
        foreach ($ls as $l) {
            foreach ($rs as $r) {
                list($ln, $lf) = array($l[0]."1", $l[1]);
                list($rn, $rf) = array($r[0]."2", $r[1]);
                $def = function() use ($ln, $lf, $rn, $rf) { $lf($ln); $rf($rn); };
                $ret[] = array($ln, $rn, $def); 
            }
        }
        return $ret;
    }

    public function some_rules_provider() {
        $vars = $this->base_variable_2tuple_provider();
        $ret = array();
        foreach ($vars as $tup) {
            list($ln, $rn, $def) = $tup;
            $ret[] = array(function() use ($ln,  $rn, $def) {
                $def();
                Dicto::$ln()->cannot()->invoke()->$rn();
            });
            $ret[] = array(function() use ($ln,  $rn, $def) {
                $def();
                Dicto::$ln()->cannot()->depend_on()->$rn();
            });
            $ret[] = array(function() use ($ln,  $rn, $def) {
                $def();
                Dicto::$ln()->must()->depend_on()->$rn();
            });
            $ret[] = array(function() use ($ln,  $rn, $def) {
                $def();
                Dicto::only()->$ln()->can()->depend_on()->$rn();
            });
            $ret[] = array(function() use ($ln,  $def) {
                $def();
                Dicto::$ln()->cannot()->contain_text("Foo");
            });
        }
        return $ret;
    }
}
