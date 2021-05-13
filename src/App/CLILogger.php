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

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class CLILogger extends AbstractLogger {
    public function log($level, $message, array $context = array()) {
        echo strtoupper($level).": ".$message."\n";
    }
}
