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

class QueryImpl implements Query {
    /**
     * @var Graph
     */
    protected $graph;

    /**
     * @var array[]
     */
    protected $steps;

    public function __construct(Graph $graph) {
        $this->graph = $graph;
        $this->steps = [];
    }

    /**
     * Expand the entities currently matched by the query.
     *
     * @param   \Closure    $expander   Entity -> Entity[]
     * @return  Query
     */
    public function expand(\Closure $expander) {
        $clone = clone $this;
        $clone->steps[] = ["expand", $expander];
        assert('$this->steps != $clone->steps');
        return $clone;
    }

    /**
     * Extract information from the currently matched query.
     *
     * @param   \Closure    $extractor  Entity -> &Result -> null
     * @return  Query
     */
    public function extract(\Closure $extractor) {
        $clone = clone $this;
        $clone->steps[] = ["extract", $extractor];
        assert('$this->steps != $clone->steps');
        return $clone;
    }

    /**
     * Run the query. The current result will be cloned in every expansion.
     *
     * @param   mixed   $result
     * @return  mixed[]
     */
    public function run($result) {
        $nodes = $this->add_result($this->graph->nodes(), $result);

        foreach ($this->steps as $step) {
            list($cmd,$clsr) = $step;
            if ($cmd == "expand") {
                $new_nodes = [];
                foreach ($nodes as $r) {
                    list($node, $result) = $r;
                    $new_nodes[] = $this->add_result($clsr($node), $result);
                }
                $nodes = call_user_func_array("array_merge", $new_nodes);
            }
            elseif ($cmd == "extract") {
                foreach ($nodes as $i => $r) {
                    list($node, $result) = $r;
                    if (is_object($result)) {
                        $clsr($node, clone $result);
                    }
                    else {
                        $clsr($node, $result);
                    }
                    $nodes[$i][1] = $result;
                }
            }
            else {
                throw new \LogicException("Unknown command: $cmd");
            }
        }

        return array_values(array_map(function($r) {
            return $r[1];
        }, $nodes));
    }

    protected function add_result(array $nodes, &$result) {
        $res = [];
        foreach ($nodes as $node) {
            $res[] = [$node, $result];
        }
        return $res;
    }

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
        $clone = new _Query;
        $clone->filters = $this->filters;
        $clone->filters[] = $filter;
        assert('$this->filters != $clone->filters');
        return $clone;
    }
}
