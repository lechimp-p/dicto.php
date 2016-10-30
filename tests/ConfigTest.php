<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\Config;

class ConfigClassTest extends PHPUnit_Framework_TestCase {
    public function test_smoke() {
        $config = new Config("/the/path", [
            [ "project" =>
                [ "root"    => "/root/dir"
                , "storage" => "/data"
                , "rules" => "/rules"
                ]
            , "analysis" =>
                [ "ignore" =>
                    [ ".*\\.omit_me"
                    ]
                ]
            ]]);
        $this->assertEquals("/rules", $config->project_rules());
        $this->assertEquals("/root/dir", $config->project_root());
        $this->assertEquals("/data", $config->project_storage());
        $this->assertEquals([".*\\.omit_me"], $config->analysis_ignore());
        $this->assertEquals("/the/path", $config->path());
    }

    public function test_defaults() {
        $config = new Config("/the/path", [
            [ "project" =>
                [ "root"    => "/root/dir"
                , "storage" => "/data"
                , "rules" => "/rules"
                ]
            ]]);

        $this->assertEquals([], $config->analysis_ignore());
        $this->assertFalse($config->analysis_store_index());
        $this->assertTrue($config->analysis_report_stdout());
        $this->assertFalse($config->analysis_report_database());
        $default_schemas =
            [ \Lechimp\Dicto\Rules\DependOn::class
            , \Lechimp\Dicto\Rules\Invoke::class
            , \Lechimp\Dicto\Rules\ContainText::class
            ];
        $this->assertEquals($default_schemas, $config->rules_schemas());
        $default_properties =
            [ \Lechimp\Dicto\Variables\Name::class
            , \Lechimp\Dicto\Variables\In::class
            ];
        $this->assertEquals($default_properties, $config->rules_properties());
        $default_variables =
            [ \Lechimp\Dicto\Variables\Namespaces::class
            , \Lechimp\Dicto\Variables\Classes::class
            , \Lechimp\Dicto\Variables\Interfaces::class
            , \Lechimp\Dicto\Variables\Traits::class
            , \Lechimp\Dicto\Variables\Functions::class
            , \Lechimp\Dicto\Variables\Globals::class
            , \Lechimp\Dicto\Variables\Files::class
            , \Lechimp\Dicto\Variables\Methods::class
            , \Lechimp\Dicto\Variables\ErrorSuppressor::class
            , \Lechimp\Dicto\Variables\Exit_::class
            , \Lechimp\Dicto\Variables\Die_::class
            , \Lechimp\Dicto\Variables\Eval_::class
            ];
        $this->assertEquals($default_variables, $config->rules_variables());
        $this->assertEquals(false, $config->runtime_check_assertions());
    }

    public function test_runtime_config() {
        $config = new Config("/the/path", [
            [ "project" =>
                [ "root"    => "/root/dir"
                , "storage" => "/data"
                , "rules" => "/rules"
                ]
            , "runtime" =>
                [ "check_assertions"  => true
                ]
            ]]);

        $this->assertEquals(true, $config->runtime_check_assertions());
    }


    public function test_merge() {
        $config = new Config("/the/path",
            [
                [ "project" =>
                    [ "storage" => "/data"
                    , "rules" => "/rules"
                    ]
                ]
            , 
                [ "project" =>
                    [ "root" => "/root/dir"
                    , "rules" => "/other_rules"
                    ]
                , "analysis" =>
                    [ "ignore" =>
                        [ ".*\\.omit_me"
                        ]
                    , "store_index" => true
                    ]
                ]
            ]);
        $this->assertEquals("/other_rules", $config->project_rules());
        $this->assertEquals("/root/dir", $config->project_root());
        $this->assertEquals("/data", $config->project_storage());
        $this->assertEquals([".*\\.omit_me"], $config->analysis_ignore());
        $this->assertTrue($config->analysis_store_index());
    }

    public function test_path_resolution() {
        $config = new Config("/the/path/", [
            [ "project" =>
                [ "root"    => "./root/dir"
                , "storage" => "./data"
                , "rules" => "./rules"
                ]
            , "analysis" =>
                [ "ignore" =>
                    [ ".*\\.omit_me"
                    ]
                ]
            ]]);
        $this->assertEquals("/the/path", $config->path());
        $this->assertEquals("/the/path/rules", $config->project_rules());
        $this->assertEquals("/the/path/root/dir", $config->project_root());
        $this->assertEquals("/the/path/data", $config->project_storage());
    }
}
