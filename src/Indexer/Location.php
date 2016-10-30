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
     * Get the handle to the file where the location is in.
     *
     * @return  mixed
     */
    public function _file();

    /**
     * Get the handle to the namespace the location is in.
     *
     * @return mixed|null
     */
    public function _namespace();

    /**
     * @return  int
     */
    public function _line();

    /**
     * @return  int
     */
    public function _column();

    /**
     * TODO: This should go away in favour of more specific methods like namespace.
     *
     * @return  array[]     List of ($entity_type, $entity_id)
     */
    public function in_entities();
}

