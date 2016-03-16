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
        $this->check_var_definitions(array($name), $definition);
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
        $this->check_single_var_definition("both", function() use ($l, $r, $def) {
            $def();
            Dicto::both()->means()->$l()->as_well_as()->$r();
        });
    }

    /**
     * @dataProvider same_base_variable_2tuple_provider 
     */
    public function test_variable_except($l, $r, $def) {
        $this->check_single_var_definition("one", function() use ($l, $r, $def) {
            $def();
            Dicto::one()->means()->$l->but_not()->$r();
        });
    }

    /**
     * @dataProvider all_base_variables_provider 
     */
    public function test_variable_with_name($name, $def) {
        $this->check_single_var_definition($name, function () use ($name, $def) {
            $var = $def();
            $var->with()->name("foo.*");
        });
    }

    /**
     * @dataProvider different_base_variable_2tuple_provider
     */
    public function test_and_only_works_on_same_type($l, $r, $def) {
        try {
            $this->check_single_var_definition("__IRRELEVANT__", function() use ($l, $r, $def) {
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
            $this->check_single_var_definition("__IRRELEVANT__", function() use ($l, $r, $def) {
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
    public function test_explain_variables($var) {
        $this->check_single_var_definition($name, function () use ($name, $def) {
            $var = $def();
            $var->$explain("EXPLANATION");
        });
    }

    /**
     * @dataProvider some_rules_provider
     */
/*    public function test_explain_rules($rule) {
        $rule2 = $rule->explain("EXPLANATION");
        $this->assertEquals(get_class($rule), get_class($rule2));
    }

    public function test_with_name_flawed_regexp() {
        try {
            $named = Dicto::_every()->_class()->_with()->_name("(foo.*");
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $_) {}
    }

    public function test_contains_text_flawed_regexp() {
        try {
            $l  = Dicto::_every()->_file();
            $l->cannot()->contain_text("(foo.*");
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $_) {}
    }*/

    public function all_base_variables_provider() {
        return array
            ( array("allClasses", function() { return Dicto::allClasses()->means()->classes(); })
            , array("allFunctions", function() { return Dicto::allFunctions()->means()->functions(); })
            , array("allBuildins", function() { return Dicto::allBuildins()->means()->buildins(); })
            , array("allGlobals", function() { return Dicto::allGlobals()->means()->globals(); })
            , array("allFiles", function() { return Dicto::allFiles()->means()->files(); })
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
            $def = function() use ($lf, $rf) { $lf(); $rf(); };
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
                $def = function() use ($lf, $rf) { $lf(); $rf(); };
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
                $def = function() use ($lf, $rf) { $lf(); $rf(); };
                $ret[] = array($ln, $rn, $def); 
            }
        }
        return $ret;
    }

/*    public function some_rules_provider() {
        $vars = $this->base_variable_2tuple_provider();
        $ret = array();
        foreach ($vars as $tup) {
            list($l, $r) = $tup;
            $ret[] = array($l->cannot()->invoke($r));
            $ret[] = array($l->cannot()->depend_on($r));
            $ret[] = array($l->must()->depend_on($r));
            $ret[] = array(Dicto::only($l)->can()->depend_on($r));
            $ret[] = array($l->cannot()->contain_text("Foo"));
        }
        return $ret;
    }*/
}
