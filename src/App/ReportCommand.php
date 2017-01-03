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
 * Command to create a report.
 */
class ReportCommand extends Command {
    /**
     * @inheritdoc
     */
    public function configure() {
        $this
            ->setName("report")
            ->setDescription
                ("Create a report."
                )
            ->setHelp
                ("This command will create a named report from the given configs."
                )
            ->addArgument
                ( "name"
                , InputArgument::REQUIRED
                , "Give the name of the report you want to create."
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

        $generator = $dic["report_generator"];
        $name = $input->getArgument("name");
        foreach ($config->reports() as $report) {
            if ($report->name() == $name) {
                $report = $report->with_target("php://stdout");
                $generator->generate($report);
            }
        }
    }
}
