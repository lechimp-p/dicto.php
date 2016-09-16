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
    protected $id;

    /**
     * @var Relation[]
     */
    protected $relations = [];

    /**
     * @param   int                 $id
     * @param   string              $type
     * @param   array<string,mixed> $properties
     */
    public function __construct($id, $type, array $properties) {
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
     * @return  null
     */
    public function add_relation($type, array $properties, Node $other) {
        $rel = $this->create_relation($type, $properties, $other);
        $this->relations[] = $rel;
    }

    protected function create_relation($type, array $properties, $other) {
        return new Relation($type, $properties, $other);
    }

    /**
     * Get the relations to other nodes.
     *
     * @return  Relation[]
     */
    public function relations() {
        return $this->relations;
    }
}
