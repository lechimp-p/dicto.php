<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class LoggerMock extends AbstractLogger {
    public $log = array();
    public function log($level, $message, array $context = array()) {
        $this->log[] = array($level, $message, $context);
    }
}
