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
    /**
     * @var RuleLoader
     */
    protected $rule_loader;

    public function __construct() {
        ini_set('xdebug.max_nesting_level', 200);
        $parser = new RuleParser
            ( array
                ( new V\Classes()
                , new V\Functions()
                , new V\Globals()
                , new V\Files()
                , new V\Methods()
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
        $this->rule_loader = new RuleLoader($parser);
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
                "Expected path to rule-file as first parameter.");
        }

        $ruleset = $this->load_rules_file($params[1]);

        // drop programm name and rule file path
        array_shift($params);
        array_shift($params);

        $configs = array();
        $this->load_configs($params, $configs);

        $dic = $this->create_dic($ruleset, $configs);

        $dic["engine"]->run();
    }

    /**
     * Load rules and initial config from a *.php-file.
     *
     * @param   string  $path
     * @return  array   ($ruleset, $config)
     */
    protected function load_rules_file($path) {
        if (!file_exists($path)) {
            throw new \RuntimeException("Unknown rule-file '$path'");
        }
        $ruleset = $this->rule_loader->load_rules_from($path);
        return $ruleset;
    }

    /**
     * Load extra configs from yaml files.
     *
     * @param   array   $config_file_paths
     * @param   array   &$configs_array
     * @return  null
     */
    protected function load_configs(array $config_file_paths, array &$configs_array) {
        foreach ($config_file_paths as $config_file) {
            if (!file_exists($config_file)) {
                throw new \RuntimeException("Unknown config-file '$config_file'");
            }
            $configs_array[] = Yaml::parse(file_get_contents($config_file));
        }
    }

    /**
     * Create and initialize the DI-container.
     *
     * @param   RuleSet     $ruleset
     * @param   array       &$configs
     * @return  Container
     */
    protected function create_dic(RuleSet $ruleset, array &$configs) {
        $container = new Container();

        $container["config"] = function () use (&$configs) {
            return new Config($configs);
        };

        $container["ruleset"] = $ruleset;

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
