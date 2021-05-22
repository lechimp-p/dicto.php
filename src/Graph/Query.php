<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

/**
 * A query on the graph.
 *
 * Starts with all nodes in the graph. Extract information from the
 * Graph by expanding the currently matched nodes and extracting
 * data from it.
 */
interface Query
{
    /**
     * Get a factory for predicates.
     *
     * @return  PredicateFactory
     */
    public function predicate_factory();

    /**
     * Expand the entities currently matched by the query.
     *
     * @param   \Closure    $expander   Entity -> Entity[]
     * @return  Query
     */
    public function expand(\Closure $expander);

    /**
     * Extract information from the currently matched query.
     *
     * @param   \Closure    $extractor  [Entity, &mixed] -> null
     * @return  Query
     */
    public function extract(\Closure $extractor);

    /**
     * Filter the current nodes.
     *
     * @param   Predicate   $predicate
     * @return  Query
     */
    public function filter(Predicate $predicate);

    /**
     * Run the query. The current result will be cloned in every expansion.
     *
     * @param   mixed   $result
     * @return  mixed[]
     */
    public function run($result);

    // Convenience Functions

    /**
     * Get nodes of some specific type.
     *
     * @param   string[] $types
     * @return  Query
     */
    public function filter_by_types(array $types);

    /**
     * Expand to relations with given types.
     *
     * @param   string[]    $types
     * @return  Query
     */
    public function expand_relations(array $types);

    /**
     * Expand to the targets of the relations.
     *
     * @return  Query
     */
    public function expand_target();
}
