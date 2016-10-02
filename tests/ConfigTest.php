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
            , "rules" =>
                [ "schemas" =>
                    [ "SomeSchema"
                    ]
                ]
            ]]);
        $this->assertEquals("/rules", $config->project_rules());
        $this->assertEquals("/root/dir", $config->project_root());
        $this->assertEquals("/data", $config->project_storage());
        $this->assertEquals([".*\\.omit_me"], $config->analysis_ignore());
        $this->assertEquals("/the/path", $config->path());
        $expected_schemas =
            [ "Lechimp\\Dicto\\Rules\\DependOn"
            , "Lechimp\\Dicto\\Rules\\Invoke"
            , "Lechimp\\Dicto\\Rules\\ContainText"
            , "SomeSchema"
            ];
        $this->assertEquals($expected_schemas, $config->rules_schemas());
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
        $default_schemas =
            [ "Lechimp\\Dicto\\Rules\\DependOn"
            , "Lechimp\\Dicto\\Rules\\Invoke"
            , "Lechimp\\Dicto\\Rules\\ContainText"
            ];
        $this->assertEquals($default_schemas, $config->rules_schemas());
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
                    ]
                ]
            ]);
        $this->assertEquals("/other_rules", $config->project_rules());
        $this->assertEquals("/root/dir", $config->project_root());
        $this->assertEquals("/data", $config->project_storage());
        $this->assertEquals([".*\\.omit_me"], $config->analysis_ignore());
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
