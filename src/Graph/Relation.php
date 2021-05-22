<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

/**
 * A relation between two nodes in the Graph. It is unidirectional.
 */
class Relation extends Entity
{
    /**
     * @var Node
     */
    private $target;

    /**
     * @param   string              $type
     * @param   array<string,mixed> $properties
     * @param   Node                $target
     */
    public function __construct($type, array $properties, Node $target)
    {
        parent::__construct($type, $properties);
        $this->target = $target;
    }

    /**
     * The target of the relation.
     *
     * @return  Node
     */
    public function target()
    {
        return $this->target;
    }
}
