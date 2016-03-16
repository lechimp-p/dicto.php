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
    public static function startDefinition() {
    }

    public static function endDefinition() {
        return new Definition\Ruleset;
    }

    public static function __callStatic($name, $arguments) {
        return new Definition\Fluid\NewVar;
    }

    public static function _every() {
        return new Definition\EveryFluid;
    }

    public static function only(Definition\Variable $var) {
        return new Definition\OnlyFluid($var);
    }
}

