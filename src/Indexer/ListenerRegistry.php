<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Indexer;

/**
 * Register listeners to an indexer.
 */
interface ListenerRegistry {
    /**
     * Add a listener for entities that get entered.
     * 
     * The provided closure gets the following arguments:
     *      * $insert   - Insert interface
     *      * $location - Location interface
     *      * $type     - of the entity according to the types in Variable
     *      * $id       - of the entity
     *      * $node     - PhpParser\Node or null if type is Variable::FILE_TYPE
     *
     * By using an array of types, one may announce to be only interested in
     * certain types of entities.
     *
     * @param   array|null  $types
     * @param   \Closure    $listener
     * @return  self
     */
    public function on_enter_entity($types, \Closure $listener);

    /**
     * Add a listener for entities that are left.
     *
     * Works like on_enter_entity.
     *
     * @param   array|null  $types
     * @param   \Closure    $listener
     * @return  self
     */
    public function on_leave_entity($types, \Closure $listener);

    /**
     * Add a listener for nodes in the AST that get entered.
     *
     * The provided closure gets the following arguments:
     *      * $insert   - Insert interface
     *      * $location - Location interface
     *      * $node     - PhpParser\Node
     *
     * By using an array of classes, one may announce to be only interested in
     * certain types of PhpParser-nodes.
     *
     * @param   array|null  $classes
     * @param   \Closure    $listener
     * @return  self
     */
    public function on_enter_misc($classes, \Closure $listener);

    /**
     * Add a listener for nodes that are left.
     *
     * Works like on_enter_misc.
     *
     * @param   array|null  $classes
     * @param   \Closure    $listener
     * @return  self
     */
    public function on_leave_misc($classes, \Closure $listener);
}

