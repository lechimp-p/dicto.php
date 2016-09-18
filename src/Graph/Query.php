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
     * @return  PathCollection
     */
    public function execute_on(Graph $graph) {
        $num = count($this->matchers);
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

            $matcher = $this->matchers[$i];
            $collection->extend(function(Path $p) use ($matcher) {
                $e = $p->last();
                if (!$matcher->matches($e)) {
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
        $collection->filter_by_last_entity(end($this->matchers));
        return $collection;
    }

    /**
     * Get a new query with an additional matcher on the next entity.
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
