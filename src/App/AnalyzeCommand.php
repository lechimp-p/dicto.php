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
     * @var Engine|null
     */
    protected $engine = null;

    /**
     * @inheritdoc
     */
    public function pull_deps_from($dic) {
        $this->engine = $dic["engine"];
    }

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
        assert('$this->engine !== null');
        $this->engine->run();
    }
}
