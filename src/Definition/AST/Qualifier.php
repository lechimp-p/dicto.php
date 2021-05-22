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
 * A qualifier is either must, cannot or only X can.
 */
class Qualifier extends Node
{
    const MUST = "MUST";
    const CANNOT = "CANNOT";
    const ONLY_X_CAN = "ONLY_X_CAN";

    /**
     * @var string
     */
    protected $which;

    public function __construct($which)
    {
        assert('in_array($which, ["MUST", "CANNOT", "ONLY_X_CAN"])');
        $this->which = $which;
    }

    /**
     * @return  string
     */
    public function which()
    {
        return $this->which;
    }
}
