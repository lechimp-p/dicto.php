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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * The App to be run from a script.
 */
class App extends Application {
    public function __construct() {
        parent::__construct();
        ini_set('xdebug.max_nesting_level', 200);
        $this->add_commands();
    }

    protected function add_commands() {
        $this->add(new AnalyzeCommand());
        $this->add(new ReportCommand());
    }

    /**
     * Overwritten from base class to load configs, condfigure the runtime
     * and build the DIC, if the command is an command specific to dicto.
     *
     * @inheritdoc
     */
    public function doRunCommand(SCommand $command, InputInterface $input, OutputInterface $output) {
        if ($command instanceof Command) {
            $command->mergeApplicationDefinition();
            if ($command->getDefinition()->hasArgument("configs")) {
                $input->bind($command->getDefinition());
                $configs = $input->getArgument("configs");
                $config = $this->load_config($configs);
                $this->configure_runtime($config);
                $dic = $this->build_dic($config);
                $command->pull_deps_from($dic);
            }
        }
        return parent::doRunCommand($command, $input, $output);
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
     * @param   Config
     * @return  DIC
     */
    protected function build_dic(Config $config) {
        return new DIC($config);
    }
}
