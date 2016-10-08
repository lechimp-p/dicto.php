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
     * @inheritdocs
     */
    public function expand(\Closure $expander) {
        $clone = clone $this;
        $clone->steps[] = ["expand", $expander];
        assert('$this->steps != $clone->steps');
        return $clone;
    }

    /**
     * @inheritdocs
     */
    public function extract(\Closure $extractor) {
        $clone = clone $this;
        $clone->steps[] = ["extract", $extractor];
        assert('$this->steps != $clone->steps');
        return $clone;
    }

    /**
     * @inheritdocs
     */
    public function filter(\Closure $filter) {
        $clone = clone $this;
        $clone->steps[] = ["filter", $filter];
        assert('$this->steps != $clone->steps');
        return $clone;
    }

    /**
     * @inheritdocs
     */
    public function run($result) {
        $nodes = $this->add_result($this->initial_nodes(), $result);

        foreach ($this->steps as $step) {
            if (count($nodes) == 0) {
                return [];
            }

            $this->switch_run_command($nodes, $step);
        }

        return array_values(array_map(function($r) {
            return $r[1];
        }, $nodes));
    }

    protected function initial_nodes() {
        return $this->graph->nodes();
    }

    protected function switch_run_command(array &$nodes, $step) {
        list($cmd,$clsr) = $step;
        if ($cmd == "expand") {
            $nodes = $this->run_expand($nodes, $clsr);
        }
        elseif ($cmd == "extract") {
            $this->run_extract($nodes, $clsr);
        }
        elseif ($cmd == "filter") {
            $nodes = $this->run_filter($nodes, $clsr);
        }
        else {
            throw new \LogicException("Unknown command: $cmd");
        }
    }

    protected function run_expand(array &$nodes, \Closure $clsr) {
        $new_nodes = [];
        foreach ($nodes as $r) {
            list($node, $result) = $r;
            $new_nodes[] = $this->add_result($clsr($node), $result);
        }
        if (count($new_nodes) == 0) {
            return [];
        }
        return call_user_func_array("array_merge", $new_nodes);
    }

    protected function run_extract(array &$nodes, \Closure $clsr) {
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

    protected function run_filter(array &$nodes, \Closure $clsr) {
        $res = [];
        foreach ($nodes as $r) {
            list($node, $result) = $r;
            if ($clsr($node, $result)) {
                $res[] = $r;
            }
        }
        return $res;
    }

    protected function add_result(array $nodes, &$result) {
        $res = [];
        foreach ($nodes as $node) {
            $res[] = [$node, $result];
        }
        return $res;
    }

    // Convenience Functions

    /**
     * @inheritdocs
     */
    public function filter_by_types(array $types) {
        return $this->filter(function(Node $n) use ($types) {
            return in_array($n->type(), $types);
        });
    }

    /**
     * @inheritdocs
     */
    public function expand_relations(array $types) {
        return $this->expand(function(Node $n) use (&$types) {
            return array_filter
                ( $n->relations()
                , function(Relation $r) use (&$types) {
                    return in_array($r->type(), $types);
                });
        });
    }

    /**
     * @inheritdocs
     */
    public function expand_target() {
        return $this->expand(function(Relation $r) {
            return [$r->target()];
        });
    }
}
