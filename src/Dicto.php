<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto;

class Dicto {
    static $new_vars = array();

    public static function startDefinition() {
        self::$new_vars = array();
    }

    public static function endDefinition() {
        return new Definition\Ruleset;
    }

    public static function only() {
        return new Definition\Fluid\Only();
    }

    public static function __callStatic($name, $arguments) {
        if (!in_array($name, self::$new_vars)) {
            self::$new_vars[] = $name;
            return new Definition\Fluid\NewVar;
        }
        else {
            return new Definition\Fluid\RuleVar;
        }
    }
}

