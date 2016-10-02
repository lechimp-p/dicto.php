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

use Lechimp\Dicto\App\RuleLoader;
use Lechimp\Dicto\Definition\RuleParser;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Rules as R;
use Lechimp\Dicto\Variables as V;
use Symfony\Component\Yaml\Yaml;
use Pimple\Container;
use PhpParser\ParserFactory;

/**
 * The App to be run from a script.
 */
class App {
    public function __construct() {
        ini_set('xdebug.max_nesting_level', 200);
    }

    /**
     * Run the app.
     *
     * @param   array   $params     from commandline
     * @return  null
     */
    public function run(array $params) {
        if (count($params) < 2) {
            throw new \RuntimeException(
                "Expected path to config-file as first parameter.");
        }

        // drop program name
        array_shift($params);

        // the rest of the params are paths to configs
        list($config_file_path, $configs) = $this->load_configs($params);
        $t = explode("/", $config_file_path);
        array_pop($t);
        $config_file_path = implode("/", $t);

        $dic = $this->create_dic($config_file_path, $configs);

        $dic["engine"]->run();
    }

    /**
     * Load extra configs from yaml files.
     *
     * @param   array   $config_file_paths
     * @return  array
     */
    protected function load_configs(array $config_file_paths) {
        $configs_array = array();
        $config_file_path = null;
        foreach ($config_file_paths as $config_file) {
            if (!file_exists($config_file)) {
                throw new \RuntimeException("Unknown config-file '$config_file'");
            }
            if ($config_file_path === null) {
                $config_file_path = $config_file;
            }
            $configs_array[] = Yaml::parse(file_get_contents($config_file));
        }
        return array($config_file_path, $configs_array);
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
     * Create and initialize the DI-container.
     *
     * @param   string      $config_file_path
     * @param   array       &$configs
     * @return  Container
     */
    protected function create_dic($config_file_path, array &$configs) {
        array('is_string($rule_file_path)');

        $container = new Container();

        $container["config"] = function () use ($config_file_path, &$configs) {
            return new Config($config_file_path, $configs);
        };

        $container["ruleset"] = function($c) {
            $rule_file_path = $c["config"]->project_rules();
            if (!file_exists($rule_file_path)) {
                throw new \RuntimeException("Unknown rule-file '$rule_file_path'");
            }
            $ruleset = $c["rule_loader"]->load_rules_from($rule_file_path);
            return $ruleset;
        };

        $container["rule_loader"] = function($c) {
            return new RuleLoader($c["rule_parser"]);
        };

        $container["rule_parser"] = function($c) {
            return new RuleParser
                ( $c["variables"]
                , $c["schemas"]
                , $c["properties"]
                );
        };

        $container["engine"] = function($c) {
            return new Engine
                ( $c["log"]
                , $c["config"]
                , $c["database_factory"]
                , $c["indexer_factory"]
                , $c["analyzer_factory"]
                , $c["source_status"]
                );
        };

        $container["log"] = function () {
            return new CLILogger();
        };

        $container["database_factory"] = function() {
            return new DBFactory();
        };

        $container["indexer_factory"] = function($c) {
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

        $container["analyzer_factory"] = function($c) {
            return new \Lechimp\Dicto\Analysis\AnalyzerFactory
                ( $c["log"]
                , $c["ruleset"]
                );
        };

        $container["php_parser"] = function() {
            return (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        };

        $container["report_generator"] = function() {
            return new CLIReportGenerator();
        };

        $container["source_status"] = function($c) {
            return new SourceStatusGit($c["config"]->project_root());
        };

        $container["schemas"] = function($c) {
            return $this->load_schemas($c["config"]->rules_schemas());
        };

        $container["properties"] = function($c) {
            return $this->load_properties($c["config"]->rules_properties());
        };

        $container["variables"] = function($c) {
            return $this->load_variables($c["config"]->rules_variables());
        };

        return $container;
    }
}
