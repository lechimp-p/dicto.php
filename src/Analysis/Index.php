<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Analysis;

use Lechimp\Dicto\Graph\Query;

/**
 * Interface to an Index of the codebase.
 */
interface Index {
    /**
     * Get a builder to create queries.
     *
     * @return  Query
     */
    public function query();
}
