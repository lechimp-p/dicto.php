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
     * Pull all dependencies from a DIC.
     *
     * @param   array|DIC   $dic
     * @return  null
     */
    abstract public function pull_deps_from($dic);
}
