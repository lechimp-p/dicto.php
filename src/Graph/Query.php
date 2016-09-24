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
     * @var \Closure[]
     */
    protected $filters = [];

    /**
     * Execute the query on a graph. Get a list of results of the
     * query with the nodes and relations establishing the path.
     *
     * @param   Graph   $graph
     * @return  PathCollection
     */
    public function execute_on(Graph $graph) {
        $num = count($this->filters);
        if ($num === 0) {
            return new PathCollection([]);
        }

        $collection = new PathCollection
            ( array_map(function($n) { return new Path($n); }
            , $graph->nodes()
            ));

        for ($i = 0; $i < $num - 1; $i++) {
            // early exit
            if ($collection->is_empty()) {
                return $collection;
            }

            $matcher = $this->filters[$i];
            $collection->extend(function(Path $p) use ($matcher) {
                $e = $p->last();
                if (!$matcher($e)) {
                    return [];
                }
                if ($e instanceof Node) {
                    return array_map(function(Relation $r) use ($p) {
                        $p2 = clone $p;
                        $p2->append($r);
                        return $p2;
                    }, $e->relations());
                }
                elseif ($e instanceof Relation) {
                    $p->append($e->target());
                    return [$p];
                }
                else {
                    throw new \LogicException("Unknown entity type: ".get_class($e));
                }
            });
        }
        $collection->filter_by_last_entity(end($this->filters));
        return $collection;
    }

    /**
     * Get a query with the given filter on the next entity.
     *
     * @param   \Closure    $filter  Entity -> bool
     * @param   Query
     */
    public function with_filter(\Closure $filter) {
        $clone = new Query;
        $clone->filters = $this->filters;
        $clone->filters[] = $filter;
        assert('$this->filters != $clone->filters');
        return $clone;
    }
}
