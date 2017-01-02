<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
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

    protected function build_dic($report_stdout = true, $report_database = false) {
        $config_params =
            [ "project" =>
                [ "root" => "./src"
                , "rules" => "./rules"
                , "storage" => tempdir()
                ]
            , "analysis" =>
                [ "report_stdout" => $report_stdout
                , "report_database" => $report_database
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

    public function test_cli_report_generator() {
        $this->assertInstanceOf(CLIReportGenerator::class, $this->dic["analysis_listener"]);
    }

    public function test_complain_on_no_analysis_listener() {
        $this->build_dic(false);

        try {
            $foo = $dic["analysis_listener"];
            $this->assertFalse("This should not happen.");
        }
        catch (\RuntimeException $e) {
            $this->assertNotInstanceOf
                ( \PHPUnit_Framework_ExpectationFailedException::class
                , $e
                );
        }
    }

    public function test_database_analysis_listener() {
        $this->build_dic(false, true);

        $this->assertInstanceOf(ResultDB::class, $this->dic["analysis_listener"]);
    }

    public function test_both_analysis_listener() {
        $this->build_dic(true, true);

        $this->assertInstanceOf(CombinedListener::class, $this->dic["analysis_listener"]);
        $listeners = $this->dic["analysis_listener"]->listeners();
        $this->assertInstanceOf(CLIReportGenerator::class, $listeners[0]);
        $this->assertInstanceOf(ResultDB::class, $listeners[1]);
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
            ( new \Lechimp\Dicto\Variables\Namespaces()
            , new \Lechimp\Dicto\Variables\Classes()
            , new \Lechimp\Dicto\Variables\Interfaces()
            , new \Lechimp\Dicto\Variables\Traits()
            , new \Lechimp\Dicto\Variables\Functions()
            , new \Lechimp\Dicto\Variables\Globals()
            , new \Lechimp\Dicto\Variables\Files()
            , new \Lechimp\Dicto\Variables\Methods()
            , new \Lechimp\Dicto\Variables\ErrorSuppressor()
            , new \Lechimp\Dicto\Variables\Exit_()
            , new \Lechimp\Dicto\Variables\Die_()
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
            , \Lechimp\Dicto\Variables\Exit_::class
            , \Lechimp\Dicto\Variables\Die_::class
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
            , new \Lechimp\Dicto\Variables\Exit_()
            , new \Lechimp\Dicto\Variables\Die_()
            , new \Lechimp\Dicto\Variables\Eval_()
            );
        $this->assertEquals($expected_variables, $variables);
    }
}
