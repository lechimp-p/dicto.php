<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Analysis\CombinedListener;
use Lechimp\Dicto\Analysis\Listener;
use Lechimp\Dicto\App\RuleLoader;
use Lechimp\Dicto\DB;
use Lechimp\Dicto\Definition\RuleBuilder;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules as R;
use Lechimp\Dicto\Variables as V;
use Lechimp\Dicto\Report;
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
            return new RuleBuilder
                ( $c["variables"]
                , $c["schemas"]
                , $c["properties"]
                );
        };

        $this["engine"] = function($c) {
            return new Engine
                ( $c["log"]
                , $c["config"]
                , $c["indexdb_factory"]
                , $c["indexer_factory"]
                , $c["analyzer_factory"]
                , $c["result_database"]
                , $c["source_status"]
                );
        };

        $this["log"] = function () {
            return new CLILogger();
        };

        $this["indexdb_factory"] = function() {
            return new DB\IndexDBFactory();
        };

        $this["indexer_factory"] = function($c) {
            return new \Lechimp\Dicto\Indexer\IndexerFactory
                ( $c["log"]
                , $c["php_parser"]
                , $c["schemas"]
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

        $this["result_database"] = function($c) {
            $config = $c["config"];
            if ($config->analysis_store_results()) {
                $path = $this->result_database_path($config);
                $connection = DB\DB::sqlite_connection($path);
            }
            else {
                $connection = DB\DB::sqlite_connection();
            }
            $db = new Report\ResultDB($connection);
            $db->maybe_init_database_schema();
            return $db;
        };

        $this["report_generator"] = function($c) {
            return new Report\Generator($c["report_queries"]);
        };

        $this["report_queries"] = function($c) {
            return new Report\Queries($c["result_database"]);
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

    // TODO: This should totally go to config.
    protected function result_database_path(Config $c) {
        return $c->project_storage()."/results.sqlite";
    }
}
