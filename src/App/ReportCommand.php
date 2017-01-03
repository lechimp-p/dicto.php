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

use Lechimp\Dicto\Report;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to create a report.
 */
class ReportCommand extends Command {
    /**
     * @var Config|null
     */
    protected $config = null;

    /**
     * @var Report\Generator|null
     */
    protected $report_generator = null;

    /**
     * @inheritdoc
     */
    public function pull_deps_from($dic) {
        $this->config = $dic["config"];
        $this->report_generator = $dic["report_generator"];
    }


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
        assert('!is_null($this->config)');
        assert('!is_null($this->report_generator)');

        $name = $input->getArgument("name");
        foreach ($this->config->reports() as $report) {
            if ($report->name() == $name) {
                $report = $report->with_target("php://stdout");
                $this->report_generator->generate($report);
            }
        }
    }
}
