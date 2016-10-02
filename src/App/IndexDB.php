<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Doctrine\DBAL\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

class IndexDB extends DB { 
    public function init_database_schema() {
    }
}
