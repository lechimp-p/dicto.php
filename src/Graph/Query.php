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
        $num = count($this->matchers);
        if ($num === 0) {
            return [];
        }

        $i = 0;
        $cur = array_map(function($n) { return [$n]; }, $graph->nodes());
        $next = [];
        while (true) {
            $matcher = $this->matchers[$i];
            $i++;

            // remove entities that does not match
            foreach ($cur as $key => $path) {
                $e = end($path);
                if (!$matcher->matches($e)) {
                    unset($cur[$key]);
                }
            }

            // this is were the end is, last matcher was checked.
            if ($i === $num) {
                return $cur;
            }

            // expand the current paths
            foreach ($cur as $path) {
                $e = end($path);
                if ($e instanceof Node) {
                    // expand relations
                    foreach ($e->relations() as $rel) {
                        $r = $path; // this _copies_ the path
                        $r[] = $rel;
                        $next[] = $r;
                    }
                }
                elseif ($e instanceof Relation) {
                    $r = $path;
                    $r[] = $e->target();
                    $next[] = $r;
                }
                else {
                    throw new \LogicException("Unknown entity type: ".get_class($e));
                }
            }

            $cur = $next;
            $next = [];
        }
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
