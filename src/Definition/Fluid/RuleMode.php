<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition\Fluid;
use Lechimp\Dicto\Definition as Def;

/**
 * Provides fluid interface to only()..can(), must() and cannot().
 */
class RuleMode extends BaseWithNameAndMode  {
    /**
     * Tell which schema to use.
     *
     * @throws  \InvalidArgumentException when schema is unknown
     * @throws  \InvalidArgumentException when there are arguments
     * @return  Base|null
     */
    public function __call($name, $arguments) {
        # ToDo: This is used in Definition\Fluid\Means as well.
        $schema = $this->rt->get_schema($name);
        if ($schema === null) {
            throw new \InvalidArgumentException("Unknown rule '$name'.");
        }
        return $schema->fluid_interface($this->rt, $this->name, $this->mode, $arguments);
    }
}
