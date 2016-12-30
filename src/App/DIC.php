<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Analysis\CombinedListener;
use Lechimp\Dicto\Analysis\Listener;
use Lechimp\Dicto\App\RuleLoader;
use Lechimp\Dicto\Definition\RuleParser;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules as R;
use Lechimp\Dicto\Variables as V;
use Pimple\Container;
use PhpParser\ParserFactory;

/**
 * The dependency injection container for the app.
 */
class DIC extends Container {
    public function __construct(Config $config) {
        $this["config"] = $config;

        $this["ruleset"] = function($c) {
            $rule_file_path = $c["config"]->project_rules();
            if (!file_exists($rule_file_path)) {
                throw new \RuntimeException("Unknown rule-file '$rule_file_path'");
            }
            $ruleset = $c["rule_loader"]->load_rules_from($rule_file_path);
            return $ruleset;
        };

        $this["rule_loader"] = function($c) {
            return new RuleLoader($c["rule_parser"]);
        };

        $this["rule_parser"] = function($c) {
            return new RuleParser
                ( $c["variables"]
                , $c["schemas"]
                , $c["properties"]
                );
        };

        $this["engine"] = function($c) {
            return new Engine
                ( $c["log"]
                , $c["config"]
                , $c["database_factory"]
                , $c["indexer_factory"]
                , $c["analyzer_factory"]
                , $c["analysis_listener"]
                , $c["source_status"]
                );
        };

        $this["log"] = function () {
            return new CLILogger();
        };

        $this["database_factory"] = function() {
            return new DBFactory();
        };

        $this["indexer_factory"] = function($c) {
            return new \Lechimp\Dicto\Indexer\IndexerFactory
                ( $c["log"]
                , $c["php_parser"]
                , array
                    ( new \Lechimp\Dicto\Rules\ContainText()
                    , new \Lechimp\Dicto\Rules\DependOn()
                    , new \Lechimp\Dicto\Rules\Invoke()
                    )
                );
        };

        $this["analyzer_factory"] = function($c) {
            return new \Lechimp\Dicto\Analysis\AnalyzerFactory
                ( $c["log"]
                , $c["ruleset"]
                );
        };

        $this["php_parser"] = function() {
            $lexer = new \PhpParser\Lexer\Emulative
                (["usedAttributes" => ["comments", "startLine", "endLine", "startFilePos"]]);
            return (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer);
        };

        $this["analysis_listener"] = function($c) {
            return $this->build_analysis_listener($c);
        };

        $this["stdout_analysis_listener"] = function() {
            return new CLIReportGenerator();
        };

        $this["database_analysis_listener"] = function($c) {
            $path = $this->result_database_path($c["config"]);
            return $c["database_factory"]->get_result_db($path);
        };

        $this["source_status"] = function($c) {
            return new SourceStatusGit($c["config"]->project_root());
        };

        $this["schemas"] = function($c) {
            return $this->load_schemas($c["config"]->rules_schemas());
        };

        $this["properties"] = function($c) {
            return $this->load_properties($c["config"]->rules_properties());
        };

        $this["variables"] = function($c) {
            return $this->load_variables($c["config"]->rules_variables());
        };

        return $this;
    }

    /**
     * Loads the schemas defined in the config.
     *
     * @param   array   $schema_classes
     * @return  R\Schema[]
     */
    protected function load_schemas(array $schema_classes) {
        $schemas = array();
        foreach ($schema_classes as $schema_class) {
            $schema = new $schema_class;
            if (!($schema instanceof R\Schema)) {
                throw new \RuntimeException("'$schema_class' is not a Schema-class.");
            }
            $schemas[] = $schema;
        }
        return $schemas;
    }

    /**
     * Loads the properties defined in the config.
     *
     * @param   array   $property_classes
     * @return  R\Schema[]
     */
    protected function load_properties(array $property_classes) {
        $properties = array();
        foreach ($property_classes as $property_class) {
            $property = new $property_class;
            if (!($property instanceof V\Property)) {
                throw new \RuntimeException("'$property_class' is not a Schema-class.");
            }
            $properties[] = $property;
        }
        return $properties;
    }

    /**
     * Loads the variables defined in the config.
     *
     * @param   array   $variable_classes
     * @return  R\Schema[]
     */
    protected function load_variables(array $variable_classes) {
        $variables = array();
        foreach ($variable_classes as $variable_class) {
            $variable = new $variable_class;
            if (!($variable instanceof V\Variable)) {
                throw new \RuntimeException("'$variable_class' is not a Schema-class.");
            }
            $variables[] = $variable;
        }
        return $variables;
    }

    /**
     * Build the listeners for analysis.
     *
     * @param   Container       $c
     * @return  Listener
     */
    public function build_analysis_listener(Container $c) {
        $config = $c["config"];
        $stdout = $config->analysis_report_stdout();
        $db = $config->analysis_report_database();
        if ($stdout && $db) {
            return new CombinedListener
                ([$c["stdout_analysis_listener"]
                , $c["database_analysis_listener"]
                ]);
        }
        elseif($stdout) {
            return $c["stdout_analysis_listener"];
        }
        elseif($db) {
            return $c["database_analysis_listener"];
        }

        throw new \RuntimeException
            ("No need to run analysis if no listener is defined.");
    }

    // TODO: This should totally go to config.
    protected function result_database_path(Config $c) {
        return $c->project_storage()."/results.sqlite";
    }
}
