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

use Lechimp\Dicto\Analysis\Variable;

/**
 * This is how to insert new entries in the index. 
 */
interface Insert {
    /**
     * Store content of a source file in the database.
     *
     * @param   string          $name
     * @param   string          $content
     * @return  null
     */
    public function source_file($name, $content);

    /**
     * Record general info about an entity.
     *
     * An entity is anything the user defined in its code like a class, a method or
     * a file, i.e. something where we know for sure how it looks like.
     *
     * Uses the same range for ids than reference, that is, each id either referers to
     * a entity or a reference.
     *
     * @param   int             $type       one of Variable::ENTITY_TYPES;
     * @param   string          $name
     * @param   string          $file
     * @param   int             $start_line
     * @param   int             $end_line
     * @return  int                         id of new entity
     */
    public function entity($type, $name, $file, $start_line, $end_line);

    /**
     * Record general info about a reference to an entity.
     *
     * A reference to an entity, buildin or global, i.e. a place where we know there
     * should be something we are refering to by name, but can not get hold of the
     * source, i.e. a function in a function call or the usage of a global. There
     * might be the possibility to dereference the reference to an entity later.
     *
     * Uses the same range for ids than entity, that is, each id either referers to
     * a entity or a reference.
     *
     * @param   int             $type
     * @param   string          $name
     * @param   string          $file       where the entity was referenced
     * @param   int             $line       where the entity was referenced
     * @return  int                         id of new reference
     */
    public function reference($type, $name, $file, $line);

    /**
     * Get the id of a reference by either inserting a new reference or reading
     * it from the cache.
     *
     * The implementation should assure that each combination of $type, $name,
     * $file and $line is only inserted once.
     *
     * @param   int             $type
     * @param   string          $name
     * @param   string          $file       where the entity was referenced
     * @param   int             $line       where the entity was referenced
     * @return  int                         id of new reference
     */
    public function get_reference($type, $name, $file, $line);

    /**
     * Record information about a relation.
     *
     * @param   string          $name   of the relation
     * @param   int             $entity_id
     * @param   int             $reference_id
     * @param   string          $file
     * @param   integer         $line
     * @return  null
     */
    public function relation($name, $entity_id, $reference_id, $file, $line);
}
