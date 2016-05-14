<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Indexer;

use Lechimp\Dicto\Analysis\Consts;

/**
 * Provides implementation for Insert::get_reference.
 */
trait CachesReferences {
    /**
     * This contains cached reference ids.
     *
     * @var array|null   string => int 
     */
    protected $reference_cache = array(); 

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
    public function get_reference($type, $name, $file, $line) {
        assert('in_array($type, \\Lechimp\\Dicto\\Analysis\\Consts::$ENTITY_TYPES)');
        assert('is_string($name)');
        assert('is_string($file)');
        assert('is_int($line)');

        // caching
        $key = $type.":".$name.":".$file.":".$line;
        if (array_key_exists($key, $this->reference_cache)) {
            return $this->reference_cache[$key];
        }

        $ref_id = $this->reference
            ( $type 
            , $name 
            , $file
            , $line
            );

        $this->reference_cache[$key] = $ref_id;
        return $ref_id;

    }

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
    abstract public function reference($type, $name, $file, $line);
}
