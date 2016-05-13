<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Rules;

use \Lechimp\Dicto\Definition as Def;

/**
 * This is what every rule needs to define.
 */
abstract class Schema {
    /**
     * Get the name of the relation.
     *
     * This must return a string without whitespaces.
     *
     * @return  string
     */
    abstract public function name(); 

    /**
     * Get the Fluid interface that should be returned on using the
     * schema.
     *
     * @param   Def\RuleDefinitionRT    $rt
     * @param   string                  $name
     * @param   string                  $mode
     * @return  Def\Fluid\Base
     */
    abstract public function fluid_interface(Def\RuleDefinitionRT $rt, $name, $mode);

    /**
     * Get a pretty printed version of the rules.
     *
     * // TODO: What is this, seriously.
     * @param   ?   $rule
     * @return  string
     */
    abstract public function pprint($rule);
}
