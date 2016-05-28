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
use Lechimp\Dicto\Variables as Vars;
use Lechimp\Dicto\Definition as Def;

/**
 * Provides fluid interface for means().
 */
class Means extends Base {
    /**
     * Talk about an existing variable or some entities.
     *
     * @throws  \InvalidArgumentException   if $arguments are passed
     * @throws  \RuntimeException           if $name is unknown are passed
     * @return  ExistingVar
     */
    public function __call($name, $arguments) {
        $entity_constructor = $this->rt->get_entities_constructor($name);
        if ($entity_constructor !== null) {
            $params = array_merge(array($this->rt->get_current_var_name()), $arguments);
            $var = call_user_func_array($entity_constructor, $params);
            $this->rt->current_var_is($var);
            return new ExistingVar($this->rt);
        }

        if (count($arguments) != 0) {
            # ToDo: This is used in Dicto::__callstatic as well.
            throw new \InvalidArgumentException(
                "No arguments are allowed for the reference to a variable '$name'.");
        }
        $this->rt->throw_on_missing_var($name);
        $this->rt->current_var_is($this->rt->get_var($name));
        return new ExistingVar($this->rt);
    }
}
