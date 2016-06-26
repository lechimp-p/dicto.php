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
use Lechimp\Dicto\Rules\Ruleset;
use Symfony\Component\Yaml\Yaml;
use Pimple\Container;
use PhpParser\ParserFactory;
use Doctrine\DBAL\DriverManager;

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
        $this->rule_loader = new RuleLoader();
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

        $configs = array();
        list($ruleset, $configs[]) = $this->load_rules_file($params[1]);

        // drop programm name and rule file path
        array_shift($params);
        array_shift($params);

        $this->load_extra_configs($params, $configs);

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
        list($ruleset, $config) = $this->rule_loader->load_rules_from($path);
        assert('is_array($config)');
        assert('$ruleset instanceof \\Lechimp\\Dicto\\Rules\\RuleSet');
        return array($ruleset, $config);
    }

    /**
     * Load extra configs from yaml files.
     *
     * @param   array   $config_file_paths
     * @param   array   &$configs_array
     * @return  null
     */
    protected function load_extra_configs(array $config_file_paths, array &$configs_array) {
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

        $container["config"] = function ($c) use (&$configs) {
            return new Config($configs);
        };

        $container["ruleset"] = $ruleset;

        $container["engine"] = function($c) {
            return new Engine
                ( $c["log"]
                , $c["config"]
                , $c["indexer"]
                , $c["analyzer"]
                );
        };

        $container["indexer_factory"] = function($c) {
            return new \Lechimp\Dicto\Indexer\IndexerFactory
                ( $c["php_parser"]
                , $c["config"]->project_root()
                );
        };

        $container["indexer"] = function($c) {
            return $c["indexer_factory"]->build
                ( $c["database"]
                , array
                    ( new \Lechimp\Dicto\Rules\ContainText()
                    , new \Lechimp\Dicto\Rules\DependOn()
                    , new \Lechimp\Dicto\Rules\Invoke()
                    )
                );
        };

        $container["php_parser"] = function($c) {
            return (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        };

        $container["database"] = function($c) {
            $db = new DB($c["connection"]);
            $db->init_sqlite_regexp();
            $db->maybe_init_database_schema();
            return $db;
        };

        $container["connection"] = function($c) {
            return DriverManager::getConnection
                ( array
                    ( "driver" => "pdo_sqlite"
                    , "memory" => false
                    , "path" => $c["config"]->project_storage()."/index.sqlite"
                    )
                );
        };

        $container["analyzer"] = function($c) {
            return new \Lechimp\Dicto\Analysis\Analyzer
                ( $c["log"]
                , $c["ruleset"]
                , $c["database"]
                , $c["report_generator"]
                );
        };

        $container["log"] = function ($c) {
            return new CLILogger();
        };

        $container["report_generator"] = function($c) {
            return new CLIReportGenerator();
        };

        return $container;
    }
}
