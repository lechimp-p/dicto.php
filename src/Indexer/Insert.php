<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Indexer;

use Lechimp\Dicto\Analysis\Consts;

/**
 * This is how to insert new entries in the index. 
 */
interface Insert {
    /**
     * Record general info about an entity.
     *
     * @param   int             $type   one of Consts::ENTITY_TYPES;
     * @param   string          $name
     * @param   string          $file
     * @param   int             $start_line
     * @param   int             $end_line
     * @param   string          $source
     * @return  int                         id of new entity
     */
    public function entity($type, $name, $file, $start_line, $end_line, $source);

    /**
     * Record information about a dependency.
     *
     * @param   int             $dependent_id
     * @param   int             $dependency_id
     * @param   int             $source_line
     * @return  null
     */
    public function dependency($dependent_id, $dependency_id, $source_line);

    /**
     * Record information about an invocation.
     *
     * @param   int             $invoker_id
     * @param   int             $invokee_id
     * @param   int             $source_line
     * @return  null
     */
    public function invokation($invoker_id, $invokee_id, $source_line);
}
