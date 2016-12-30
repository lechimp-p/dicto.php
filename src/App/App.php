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

        $config = $this->load_config($params);
        $dic = $this->build_dic($config);
        $this->configure_runtime($config);


        $dic["engine"]->run();
    }

    /**
     * Configure php runtime.
     *
     * @param   Config  $config
     * @return  null
     */
    protected function configure_runtime(Config $config) {
        if ($config->runtime_check_assertions()) {
            assert_options(ASSERT_ACTIVE, true);
            assert_options(ASSERT_WARNING, true);
            assert_options(ASSERT_BAIL, false);
        }
        else {
            assert_options(ASSERT_ACTIVE, false);
            assert_options(ASSERT_WARNING, false);
            assert_options(ASSERT_BAIL, false);
        }
    }

    /**
     * Load extra configs from yaml files.
     *
     * @param   array   $config_file_paths
     * @return  array
     */
    protected function load_config(array $config_file_paths) {
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

        $t = explode("/", $config_file_path);
        array_pop($t);
        $config_file_path = implode("/", $t);

        return new Config($config_file_path, $configs_array);
    }

    /**
     * Build the dependency injection container.
     *
     * @return DIC
     */
    protected function build_dic(Config $config) {
        return new DIC($config);
    }
}
