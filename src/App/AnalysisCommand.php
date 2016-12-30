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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Command to run an analysis.
 */
class AnalysisCommand extends Command {
    /**
     * @inheritdoc
     */
    public function configure() {
        $this
            ->setName("analysis")
            ->setDescription
                ("Runs an analysis"
                )
            ->setHelp
                ("This command will index a codebase and analyze it, according to "
                ."the given configs."
                )
            ->addArgument
                ( "configs"
                , InputArgument::IS_ARRAY
                , "Give paths to config files, separated by spaces."
                );
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $config_paths = $input->getArgument("configs");
        if (count($config_paths) == 0) {
            $output->writeLn("<error>You need to give the path to at least one config.</error>");
            return;
        }
        $config = $this->load_config($config_paths);
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
