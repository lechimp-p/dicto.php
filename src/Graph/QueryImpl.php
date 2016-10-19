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

    /**
     * @var PredicateFactory
     */
    protected $predicate_factory;

    public function __construct(Graph $graph) {
        $this->graph = $graph;
        $this->steps = [];
        $this->predicate_factory = new PredicateFactory();
    }

    /**
     * @inheritdocs
     */
    public function predicate_factory() {
        return $this->predicate_factory;
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
    public function filter(Predicate $predicate) {
        $clone = clone $this;
        if (count($this->steps) == 0) {
            // On the first step we keep the predicate to later put it into
            // graph->nodes.
            $clone->steps[] = ["filter", $predicate];
        }
        else {
            // For later steps, we already compile the predicate to only
            // compile it once.
            $clone->steps[] = ["filter", $predicate->compile()];
        }
        assert('$this->steps != $clone->steps');
        return $clone;
    }

    /**
     * @inheritdocs
     */
    public function run($result) {
        $steps = $this->steps;
        if (count($steps) > 0 && $steps[0][0] == "filter") {
            $nodes = $this->graph->nodes($steps[0][1]);
            array_shift($steps);
        }
        else {
            $nodes = $this->graph->nodes();
        }
        $nodes = $this->add_result($nodes, $result);

        foreach ($steps as $step) {
            $nodes = $this->switch_run_command($nodes, $step);
        }

        $res = array();
        while ($nodes->valid()) {
            $val = $nodes->current();
            $res[] = $val[1];
            $nodes->next();
        }
        return $res;
    }

    /**
     * @return  Iterator <[Node,mixed]>
     */
    protected function switch_run_command(\Iterator $nodes, $step) {
        list($cmd,$par) = $step;
        if ($cmd == "expand") {
            return $this->run_expand($nodes, $par);
        }
        elseif ($cmd == "extract") {
            return $this->run_extract($nodes, $par);
        }
        elseif ($cmd == "filter") {
            return $this->run_filter($nodes, $par);
        }
        else {
            throw new \LogicException("Unknown command: $cmd");
        }
    }

    /**
     * @return  Iterator <[Node,mixed]>
     */
    protected function run_expand(\Iterator $nodes, \Closure $clsr) {
        while ($nodes->valid()) {
            list($node, $result) = $nodes->current();
            // TODO: let closure return an Iterator too.
            foreach($clsr($node) as $new_node) {
                yield [$new_node, $result];
            }
            $nodes->next();
        }
    }

    /**
     * @return  Iterator <[Node,mixed]>
     */
    protected function run_extract(\Iterator $nodes, \Closure $clsr) {
        while ($nodes->valid()) {
            list($node, $result) = $nodes->current();
            if (is_object($result)) {
                $result = clone($result);
            }
            $clsr($node, $result);
            yield [$node, $result];
            $nodes->next();
        }
    }

    /**
     * @return  Iterator <[Node,mixed]>
     */
    protected function run_filter(\Iterator $nodes, \Closure $predicate) {
        while ($nodes->valid()) {
            $val = $nodes->current();
            list($node, $result) = $val;
            if ($predicate($node, $result)) {
                yield $val;
            }
            $nodes->next();
        }
    }

    /**
     * @return  Iterator <[Node,mixed]>
     */
    protected function add_result(\Iterator $nodes, &$result) {
        while ($nodes->valid()) {
            $node = $nodes->current();
            yield [$node, $result];
            $nodes->next();
        }
    }

    // Convenience Functions

    /**
     * @inheritdocs
     */
    public function filter_by_types(array $types) {
        $f = $this->predicate_factory();
        return $this->filter
            ( $f->_and
                ( array_map
                    ( function($t) use ($f) {
                            return $f->_type_is($t);
                        }
                    , $types
                    )
                )
            );
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
