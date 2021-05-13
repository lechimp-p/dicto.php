<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition\AST;

/**
 * An any-definition.
 */
class Any extends Definition { 
    /**
     * @var Definition[]
     */
    protected  $definitions;

    public function __construct(array $definitions) {
        $this->definitions = array_map(function (Definition $d) {
            return $d;
        }, $definitions);
    }

    /**
     * @return Definition[]
     */
    public function definitions() {
        return $this->definitions;
    }
}
