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

use Lechimp\Dicto\Graph\Predicate;

/**
 * The complete graph.
 */
class Graph {
    /**
     * @var array<string, array<int, Node>>
     */
    protected $nodes = [];

    /**
     * @var int
     */
    protected $id_counter = 0;

    /**
     * Create a new node in the graph.
     *
     * @param   string                      $type
     * @param   array<string,mixed>|null    $properties
     * @return  Node
     */
    public function create_node($type, array $properties = null) {
        $node = $this->build_node($this->id_counter, $type, $properties);
        if (!array_key_exists($type, $this->nodes)) {
            $this->nodes[$type] = [];
        }
        $this->nodes[$type][$this->id_counter] = $node;
        $this->id_counter++;
        return $node;
    }

    protected function build_node($id, $type, array $properties = null) {
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
     * Get nodes from the graph, maybe filtered by a filter.
     *
     * @param   Predicate|null    $filter
     * @return  Iterator <Node>
     */
    public function nodes(Predicate $filter = null) {
        if ($filter !== null) {
            $types = $filter->for_types(array_keys($this->nodes));
            $filter = $filter->compile();
        }
        else {
            $types = array_keys($this->nodes);
        }
        foreach ($this->nodes as $type => $nodes) {
            if (!in_array($type, $types)) {
                continue;
            }
            foreach ($nodes as $node) {
                if ($filter === null) {
                    yield $node;
                }
                else {
                    if ($filter($node)) {
                        yield $node;
                    }
                }
            }
        }
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
        foreach ($this->nodes as $nodes) {
            if (array_key_exists($id, $nodes)) {
                return $nodes[$id];
            }
        }
        throw new \InvalidArgumentException("Unknown node id '$id'");
    }

    /**
     * Build a query on the graph.
     *
     * @return  Query
     */
    public function query() {
        return new QueryImpl($this);
    }
}
