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
        $config = new Config([
            [ "project" =>
                [ "root"    => "/root/dir"
                , "storage" => "/data"
                ]
            , "analysis" =>
                [ "ignore" =>
                    [ ".*\\.omit_me"
                    ]
                ]
            ]]);
        $this->assertEquals("/root/dir", $config->project_root());
        $this->assertEquals("/data", $config->project_storage());
        $this->assertEquals([".*\\.omit_me"], $config->analysis_ignore());
    }

    public function test_merge() {
        $config = new Config(
            [
                [ "project" =>
                    [ "storage" => "/data"
                    ]
                ]
            , 
                [ "project" =>
                    [ "root" => "/root/dir"
                    ]
                , "analysis" =>
                    [ "ignore" =>
                        [ ".*\\.omit_me"
                        ]
                    ]
                ]
            ]);
        $this->assertEquals("/root/dir", $config->project_root());
        $this->assertEquals("/data", $config->project_storage());
        $this->assertEquals([".*\\.omit_me"], $config->analysis_ignore());
    }

}
