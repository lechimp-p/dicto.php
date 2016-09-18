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
 * A path through the graph. Starts at a node, then alternates between
 * relations and nodes.
 */
class Path {
    /**
     * @var Entity[]
     */
    protected $entities;

    /**
     * @var bool
     */
    protected $next_should_be_node;

    public function __construct(Node $start) {
        $this->entities = [$start];
        $this->next_should_be_node = false;
    }

    /**
     * Append the given entity. Enforces the node/relation alternation.
     *
     * @param   Entity  $entity
     * @throw   \InvalidArgumentException   if alternation is not correct. 
     * @return  null
     */
    public function append(Entity $entity) {
        if ($this->next_should_be_node && !($entity instanceof Node)) {
            throw new \InvalidArgumentException(
                    "Expected Node, got ".get_class($entity));
        }
        if (!$this->next_should_be_node && !($entity instanceof Relation)) {
            throw new \InvalidArgumentException(
                    "Expected Relation, got ".get_class($entity));
        }
        $this->next_should_be_node = !$this->next_should_be_node;
        $this->entities[] = $entity;
    }

    /**
     * Get the last entity in the path.
     *
     * @return  Entity
     */
    public function last() {
        return end($this->entities);
    }

    /**
     * Get all entities in the path.
     *
     * @return Entity[]
     */
    public function entities() {
        return $this->entities;
    }

    /**
     * Extract information from the path.
     *
     * @param   \Closure    $extractor  Entity (-> Entity ...) -> mixed
     * @return  mixed
     */
    public function extract(\Closure $extractor) {
        return call_user_func_array($extractor, $this->entities);
    }
}
