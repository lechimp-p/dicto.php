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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to run an analysis.
 */
class AnalyzeCommand extends Command {
    /**
     * @inheritdoc
     */
    public function configure() {
        $this
            ->setName("analyze")
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
}
