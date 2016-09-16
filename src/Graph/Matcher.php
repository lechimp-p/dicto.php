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
 * An Matcher matches an Entity or not.
 */
class Matcher {
    /**
     * @var \Closure
     */
    protected $condition;

    /**
     * @param   \Closure    $condition  Entity -> bool
     */
    public function __construct(\Closure $condition) {
        $this->condition = $condition;
    }

    /**
     * Does the entity match?
     *
     * @param   Entity  $entity
     * @return  bool
     */
    public function matches(Entity $entity) {
        $condition = $this->condition;
        return $condition($entity);
    }
}
