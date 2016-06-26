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
        $config = new Config(array(array
            ( "project" => array
                ( "root"    => "/root/dir"
                , "storage" => "/data"
                )
            , "analysis" => array
                ( "ignore" => array
                    ( ".*\\.omit_me"
                    )
                )
            )
        ));
        $this->assertEquals("/root/dir", $config->project_root());
        $this->assertEquals("/data", $config->project_storage());
        $this->assertEquals(array(".*\\.omit_me"), $config->analysis_ignore());
    }

    public function test_merge() {
        $config = new Config(array
            ( array
                ( "project" => array
                    ( "storage" => "/data"
                    )
                )
            , array
                ( "project" => array
                    ( "root" => "/root/dir"
                    )
                , "analysis" => array
                    ( "ignore" => array
                        ( ".*\\.omit_me"
                        )
                    )
                )
            )
        );
        $this->assertEquals("/root/dir", $config->project_root());
        $this->assertEquals("/data", $config->project_storage());
        $this->assertEquals(array(".*\\.omit_me"), $config->analysis_ignore());
    }

}
