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
        if (count($params) < 3) {
            throw new \RuntimeException(
                "Expected path to rule-file as first parameter and path to config as second parameter.");
        }

        // drop program name
        array_shift($params);

        $rules_file = array_shift($params);

        $configs = $this->load_configs($params);

        $dic = $this->create_dic($rules_file, $configs);

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
        foreach ($config_file_paths as $config_file) {
            if (!file_exists($config_file)) {
                throw new \RuntimeException("Unknown config-file '$config_file'");
            }
            $configs_array[] = Yaml::parse(file_get_contents($config_file));
        }
        return $configs_array;
    }

    /**
     * Create and initialize the DI-container.
     *
     * TODO: move rule_file_path to config?
     *
     * @param   string      $rule_file_path
     * @param   array       &$configs
     * @return  Container
     */
    protected function create_dic($rule_file_path, array &$configs) {
        array('is_string($rule_file_path)');

        $container = new Container();

        $container["config"] = function () use (&$configs) {
            return new Config($configs);
        };

        $container["ruleset"] = function($c) use (&$rule_file_path) {
            if (!file_exists($rule_file_path)) {
                throw new \RuntimeException("Unknown rule-file '$rule_file_path'");
            }
            $ruleset = $c["rule_loader"]->load_rules_from($rule_file_path);
            return $ruleset;
        };

        $container["rule_loader"] = function($c) {
            return new RuleLoader($c["rule_parser"]);
        };

        $container["rule_parser"] = function() {
            // TODO: Move this stuff to the config.
            return new RuleParser
                ( array
                    ( new V\Classes()
                    , new V\Functions()
                    , new V\Globals()
                    , new V\Files()
                    , new V\Methods()
                    , new V\LanguageConstruct("ErrorSuppressor", "@")
                    // TODO: Add some language constructs here...
                    )
                , array
                    ( new R\ContainText()
                    , new R\DependOn()
                    , new R\Invoke()
                    )
                , array
                    ( new V\Name()
                    )
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

        return $container;
    }
}
