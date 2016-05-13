<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Definition\Fluid;
use Lechimp\Dicto\Definition as Def;

/**
 * Provides fluid interface to only()..can(), must() and cannot().
 */
class RuleMode extends BaseWithNameAndMode  {
    public function contain_text($regexp) {
        $var = $this->rt->get_var($this->name);
        $this->rt->add_rule(
            new Def\Rules\ContainText($this->mode, $var, $regexp));
   }

    /**
     * Tell which schema to use.
     *
     * @throws  \InvalidArgumentException when schema is unknown
     * @throws  \InvalidArgumentException when there are arguments
     * // TODO: What does this return?
     * @return  ?
     */
    public function __call($name, $arguments) {
        # ToDo: This is used in Definition\Fluid\Means as well.
        if (count($arguments) != 0) {
            throw new \InvalidArgumentException(
                "No arguments are allowed when using a rule schema.");
        }
        $schema = $this->rt->get_schema($name);
        if ($schema === null) {
            throw new \InvalidArgumentException("Unknown rule '$name'.");
        }
        return $schema->fluid_interface($this->rt, $this->name, $this->mode);
    }
}
