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
 * A query on the graph.
 *
 * Starts with all nodes in the graph. Extract information from the
 * Graph by expanding the currently matched nodes and extracting
 * data from it.
 */
interface Query {
    /**
     * Expand the entities currently matched by the query.
     *
     * @param   Entity  $match
     * @return  Entity[]
     */
    public function expand(Entity $entity);

    /**
     * Extract information from the currently matched query.
     *
     * @param   Entity  $match
     * @param   mixed   $result
     * @return  null
     */
    public function extract(Entity $entity);

    /**
     * Run the query. The current result will be cloned in every expansion.
     *
     * @param   mixed   $result
     * @return  mixed[]
     */
    public function run($result = new StdObject());
}
