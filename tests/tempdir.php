<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

function tempdir() {
    $name = tempnam(sys_get_temp_dir(), "php-dicto");
    if (file_exists($name)) {
        unlink($name);
    }
    mkdir($name);
    assert('is_dir($name)');
    return $name;
}
