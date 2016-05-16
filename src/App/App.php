<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\App\RuleLoader;

use Symfony\Component\Yaml\Yaml;
use Pimple\Container;
use PhpParser\ParserFactory;
use Doctrine\DBAL\DriverManager;

/**
 * The App to be run from a script.
 */
class App {
    /**
     * @var Container
     */
    protected $dic;

    public function __construct(\Closure $postprocess_dic = null) {
        ini_set('xdebug.max_nesting_level', 200);

        if ($postprocess_dic === null) {
            $postprocess_dic = function($c) { return $c; };
        }
        $this->dic = $this->create_dic($postprocess_dic); 
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

        $this->dic["config"] = $this->create_config($configs);
        $this->dic["ruleset"] = $ruleset;
        $this->dic["engine"]->run();
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
        $rule_loader = $this->dic["rule_loader"];
        list($ruleset, $config) = $rule_loader->load_rules_from($path);
        assert('is_array($config)');
        assert('$ruleset instanceof \\Lechimp\\Dicto\\Definition\\RuleSet');
        return array($ruleset, $config);
    }

    /**
     * Load extra configs from yaml files.
     *
     * @param   array   $config_file_paths
     * @param   array&  $configs_array
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
     * Create a validated config from arrays containing config chunks.
     *
     * @param   array[] $configs
     * @return  Config
     */
    protected function create_config(array $configs) {
        return new Config($configs);
    }

    /**
     * Create and initialize the DI-container.
     *
     * @param   \Closure    $postprocess_dic    Closure to postprocess the DI.
     * @return  Container
     */
    protected function create_dic(\Closure $postprocess_dic) {
        $container = new Container();

        $container["rule_loader"] = function($c) {
            return new RuleLoader();
        };

        $container["engine"] = function($c) {
            return new Engine
                ( $c["config"]
                , $c["indexer"]
                , $c["analyzer"]
                );
        };

        $container["indexer"] = function($c) {
            return new \Lechimp\Dicto\Indexer\Indexer
                ( $c["php_parser"]
                , $c["config"]->project_root()
                , $c["database"]
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
                    , "memory" => $c["config"]->sqlite_memory()
                    , "path" => $c["config"]->sqlite_path()
                    )
                );
        };

        $container["analyzer"] = function($c) {
            return new \Lechimp\Dicto\Analysis\Analyzer
                ( $c["ruleset"]
                , $c["database"]
                , $c["report_generator"]
                );
        };

        $container["report_generator"] = function($c) {
            return new CLIReportGenerator();
        };

        $container = $postprocess_dic($container);
        if (!($container instanceof Container)) {
            throw new \RuntimeException
                ("DIC postprocessor did not return a Container.");
        }
        return $container;
    }
}
