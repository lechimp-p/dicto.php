<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Indexer;

/**
 * Provides information on the location where something was detected.
 */
interface Location {
    /**
     * @return mixed
     */
    public function file();

    /**
     * @return  array[]     List of ($entity_type, $entity_id)
     */
    public function in_entities();
}

