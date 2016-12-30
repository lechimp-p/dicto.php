#!/usr/bin/env hhvm
<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

require_once(__DIR__."/vendor/autoload.php");

use Symfony\Component\Console\Application;

$app = new \Lechimp\Dicto\App\App();

$app->setAutoExit(false);
$start_time = microtime(true);
$app->run();
$time_elapsed_secs = microtime(true) - $start_time;

echo "execution time:    ".$time_elapsed_secs."s\n";
echo "peak memory usage: ".number_format(memory_get_peak_usage(), 0, ".", ".")." byte\n";

