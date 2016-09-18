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
 * The complete graph.
 */
class Graph {
    /**
     * @var array<int, Node>
     */
    protected $nodes = [];

    /**
     * @var int
     */
    protected $id_counter = 0;

    /**
     * Create a new node in the graph.
     *
     * @param   string              $type
     * @param   array<string,mixed> $properties
     * @return  Node
     */
    public function create_node($type, array $properties) {
        $node = $this->build_node($this->id_counter, $type, $properties);
        $this->nodes[] = $node;
        $this->id_counter++;
        return $node;
    }

    protected function build_node($id, $type, array $properties) {
        return new Node($id, $type, $properties);
    }

    /**
     * Add a relation to the graph.
     *
     * @param   Node                $left
     * @param   string              $type
     * @param   array<string,mixed> $properties
     * @param   Node                $right
     * @return  Relation
     */
    public function add_relation(Node $left, $type, array $properties, Node $right) {
        return $left->add_relation($type, $properties, $right);
    }

    /**
     * Get nodes from the graph, maybe filtered by a matcher.
     *
     * @param   Matcher|null    $matcher
     * @return  Node[]
     */
    public function nodes(Matcher $matcher = null) {
        if ($matcher === null) {
            return $this->nodes;
        }
        return array_filter($this->nodes, function(Node $n) use ($matcher) {
            return $matcher->matches($n);
        });
    }

    /**
     * Get the node with the given id.
     *
     * @param   int     $id
     * @throws  \InvalidArgumentException   if $id is unknown
     * @return  Node
     */
    public function node($id) {
        assert('is_int($id)');
        if (!array_key_exists($id, $this->nodes)) {
            throw new \InvalidArgumentException("Unknown node id '$id'");
        }
        return $this->nodes[$id];
    }
}
