<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\DIC;
use Lechimp\Dicto\App\Config;
use Lechimp\Dicto\App\CLIReportGenerator;
use Lechimp\Dicto\Indexer\IndexerFactory;
use Lechimp\Dicto\App\Engine;
use Lechimp\Dicto\App\RuleLoader;
use Lechimp\Dicto\App\SourceStatus;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Report\ResultDB;
use Lechimp\Dicto\Report;
use Lechimp\Dicto\Analysis\CombinedListener;

require_once(__DIR__."/tempdir.php");

class _DIC extends DIC {
    public function _load_schemas(array $schema_classes) {
        return $this->load_schemas($schema_classes);
    }

    public function _load_properties(array $property_classes) {
        return $this->load_properties($property_classes);
    }

    public function _load_variables(array $variable_classes) {
        return $this->load_variables($variable_classes);
    }
}

class DICTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->build_dic();
    }

    protected function build_dic($store_results = true) {
        $this->storage = tempdir();
        $config_params =
            [ "project" =>
                [ "root" => "./src"
                , "rules" => "./rules"
                , "storage" => $this->storage
                ]
            , "analysis" =>
                [ "store_results" => $store_results
                , "ignore" =>
                    [ ".*\\.omit_me"
                    ]
                ]
            ];
        $config = new Config(__DIR__."/data", [$config_params]);
        $this->dic = new _DIC($config);
    }

    public function test_indexer_factory() {
        $this->assertInstanceOf(IndexerFactory::class, $this->dic["indexer_factory"]);
    }

    public function test_engine() {
        $this->assertInstanceOf(Engine::class, $this->dic["engine"]);
    }

    public function test_source_status() {
        $this->assertInstanceOf(SourceStatus::class, $this->dic["source_status"]);
    }

    public function test_report_generator() {
        $this->assertInstanceOf(Report\Generator::class, $this->dic["report_generator"]);
    }

    public function test_schemas() {
        $expected_schemas = array
            ( new \Lechimp\Dicto\Rules\DependOn
            , new \Lechimp\Dicto\Rules\Invoke
            , new \Lechimp\Dicto\Rules\ContainText
            );
        $this->assertEquals($expected_schemas, $this->dic["schemas"]);
    }

    public function test_load_schemas() {
        $default_schemas =
            [ \Lechimp\Dicto\Rules\DependOn::class
            , \Lechimp\Dicto\Rules\Invoke::class
            , \Lechimp\Dicto\Rules\ContainText::class
            ];
        $schemas = $this->dic->_load_schemas($default_schemas);
        $expected_schemas = array
            ( new \Lechimp\Dicto\Rules\DependOn
            , new \Lechimp\Dicto\Rules\Invoke
            , new \Lechimp\Dicto\Rules\ContainText
            );
        $this->assertEquals($expected_schemas, $schemas);
    }

    public function test_properties() {
        $expected_properties =
            [ new \Lechimp\Dicto\Variables\Name
            , new \Lechimp\Dicto\Variables\In
            ];
        $this->assertEquals($expected_properties, $this->dic["properties"]);
    }

    public function test_load_properties() {
        $default_properties =
            [ \Lechimp\Dicto\Variables\Name::class
            , \Lechimp\Dicto\Variables\In::class
            ];
        $properties = $this->dic->_load_properties($default_properties);
        $expected_properties =
            [ new \Lechimp\Dicto\Variables\Name
            , new \Lechimp\Dicto\Variables\In
            ];
        $this->assertEquals($expected_properties, $properties);
    }

    public function test_variables() {
        $expected_variables = array
            ( new \Lechimp\Dicto\Variables\Everything()
            , new \Lechimp\Dicto\Variables\Namespaces()
            , new \Lechimp\Dicto\Variables\Classes()
            , new \Lechimp\Dicto\Variables\Interfaces()
            , new \Lechimp\Dicto\Variables\Traits()
            , new \Lechimp\Dicto\Variables\Functions()
            , new \Lechimp\Dicto\Variables\Globals()
            , new \Lechimp\Dicto\Variables\Files()
            , new \Lechimp\Dicto\Variables\Methods()
            , new \Lechimp\Dicto\Variables\ErrorSuppressor()
            , new \Lechimp\Dicto\Variables\ExitOrDie()
            , new \Lechimp\Dicto\Variables\Eval_()
            );
        $this->assertEquals($expected_variables, $this->dic["variables"]);
    }

    public function test_load_variables() {
        $default_variables = array
            ( \Lechimp\Dicto\Variables\Classes::class
            , \Lechimp\Dicto\Variables\Functions::class
            , \Lechimp\Dicto\Variables\Globals::class
            , \Lechimp\Dicto\Variables\Files::class
            , \Lechimp\Dicto\Variables\Methods::class
            , \Lechimp\Dicto\Variables\ErrorSuppressor::class
            , \Lechimp\Dicto\Variables\ExitOrDie::class
            , \Lechimp\Dicto\Variables\Eval_::class
            );
        $variables = $this->dic->_load_variables($default_variables);
        $expected_variables = array
            ( new \Lechimp\Dicto\Variables\Classes()
            , new \Lechimp\Dicto\Variables\Functions()
            , new \Lechimp\Dicto\Variables\Globals()
            , new \Lechimp\Dicto\Variables\Files()
            , new \Lechimp\Dicto\Variables\Methods()
            , new \Lechimp\Dicto\Variables\ErrorSuppressor()
            , new \Lechimp\Dicto\Variables\ExitOrDie()
            , new \Lechimp\Dicto\Variables\Eval_()
            );
        $this->assertEquals($expected_variables, $variables);
    }

    public function test_result_database() {
        $this->assertInstanceOf(ResultDB::class, $this->dic["result_database"]);
        $params = $this->dic["result_database"]->connection()->getParams();
        $this->assertEquals($this->storage."/results.sqlite", $params["path"]);
        $this->assertFalse($params["memory"]);

        $this->build_dic(false);
        $params = $this->dic["result_database"]->connection()->getParams();
        $this->assertFalse(isset($params["path"]));
        $this->assertTrue($params["memory"]);
    }
}
