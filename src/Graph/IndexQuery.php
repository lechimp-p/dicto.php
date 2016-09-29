<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

/**
 * A query on the IndexDB.
 */
interface IndexQuery extends Query {
    /**
     * Get nodes of some specific type.
     *
     * TODO: This could go to Query.
     *
     * @param   string[] $type
     * @return  IndexQuery
     */
    public function filter_by_types($types);

    /**
     * Get files only.
     *
     * TODO: Remove this, for testing only.
     *
     * @return Query
     */
    public function files();

    /**
     * Get classes only.
     *
     * TODO: Remove this, for testing only.
     *
     * @return Query
     */
    public function classes();

    /**
     * Get methods only.
     *
     * TODO: Remove this, for testing only.
     *
     * @return Query
     */
    public function methods();

    /**
     * Get functions only.
     *
     * TODO: Remove this, for testing only.
     *
     * @return Query
     */
    public function functions();

    /**
     * Expand to relations with given types.
     *
     * TODO: rename to expand_relations
     * TODO: This could go to query
     *
     * @param   string[]    $types
     * @return  Query
     */
    public function expand_relation(array $types);

    /**
     * Expand to the targets of the relations.
     *
     * TODO: This could go to query
     *
     * @return  Query
     */
    public function expand_target();
}
