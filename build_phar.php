#!/usr/bin/env php
<?php

/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

$base_dir = __DIR__;

$src_dir = "$base_dir/src";
$vendor_dir = "$base_dir/vendor";
$dicto_path = "$base_dir/dicto.php";

$build_dir = __DIR__;
$phar_name = "dicto.phar";
$phar_path = "$build_dir/$phar_name";

// Remove previously created phar if one exists.
if (file_exists($phar_path)) {
    unlink($phar_path);
}

$phar = new Phar(
        $phar_path,
        FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
        $phar_name
    );

$phar->buildFromDirectory($base_dir);

$phar->setStub(
    <<<STUB
#!/usr/bin/env php
<?php
Phar::mapPhar();
include "phar://$phar_name/dicto.php";
__HALT_COMPILER();
STUB
);

chmod($phar_path, 0755);
