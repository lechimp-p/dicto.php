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

use Symfony\Component\Console\Command\Command as SCommand;
use Symfony\Component\Yaml\Yaml;

/**
 * Base class for Commands.
 */
abstract class Command extends SCommand {
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
