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

use Symfony\Component\Console\Application;

/**
 * The App to be run from a script.
 */
class App extends Application {
    public function __construct() {
        parent::__construct();
        ini_set('xdebug.max_nesting_level', 200);

        $this->add(new AnalysisCommand());
        $this->add(new ReportCommand());
    }
}
