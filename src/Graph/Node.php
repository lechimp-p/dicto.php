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
 * A node in the graph. It has an id to make it identifiable. It also
 * has some relations to other nodes.
 */
class Node extends Entity {
    /**
     * @var int
     */
    private $id;

    /**
     * @var Relation[]
     */
    private $relations = [];

    /**
     * @param   int                         $id
     * @param   string                      $type
     * @param   array<string,mixed>|null    $properties
     */
    public function __construct($id, $type, array $properties = null) {
        assert('is_int($id)');
        $this->id = $id;
        parent::__construct($type, $properties);
    }

    /**
     * Get the id.
     *
     * @return  int 
     */
    public function id() {
        return $this->id;
    }

    /**
     * Add a relation from this graph to another.
     *
     * @param   string              $type
     * @param   array<string,mixed> $properties
     * @param   Node                $other
     * @return  Relation
     */
    public function add_relation($type, array $properties, Node $other) {
        $rel = $this->build_relation($type, $properties, $other);
        $this->relations[] = $rel;
        return $rel;
    }

    protected function build_relation($type, array $properties, $other) {
        return new Relation($type, $properties, $other);
    }

    /**
     * Get the relations to other nodes.
     *
     * @param   \Closure|null   $filter
     * @return  Relation[]
     */
    public function relations(\Closure $filter = null) {
        if ($filter !== null) {
            return array_values(array_filter($this->relations, $filter));
        }
        else {
            return $this->relations;
        }
    }

    /**
     * Get all related nodes, where relations might be filtered.
     *
     * @param   \Closure|null   $filter
     * @return  Node[]
     */
    public function related_nodes(\Closure $filter = null) {
        $filtered = $this->relations($filter);
        $get_node = function($r) { return $r->target(); };
        return array_values(array_map($get_node, $filtered));
    }
}
