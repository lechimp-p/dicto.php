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
 * A query on the graph. Finds specific paths in the graph.
 */
class Query {
    /**
     * @var Matcher[]
     */
    protected $matchers = [];

    /**
     * Execute the query on a graph. Get a list of results of the
     * query with the nodes and relations establishing the path.
     *
     * @param   Graph   $graph
     * @return  array[]
     */
    public function execute_on(Graph $graph) {
        $res = array();
        foreach ($graph->nodes() as $node) {
            if ($this->matchers[0]->matches($node)) {
                $res[] = [$node];
            }
        }
        return $res;
    }

    /**
     * Get a new query with an additional matcher on an entity.
     *
     * @param   Matcher $matcher
     * @return  Query
     */
    public function with_matcher(Matcher $matcher) {
        $clone = new Query;
        $clone->matchers = $this->matchers;
        $clone->matchers[] = $matcher;
        assert('$this->matchers != $clone->matchers');
        return $clone;
    }

    /**
     * Get a query with the given condition on the next entity.
     *
     * @param   \Closure    $condition  Entity -> bool
     * @param   Query
     */
    public function with_condition(\Closure $condition) {
        return $this->with_matcher(new Matcher($condition));
    }
}
