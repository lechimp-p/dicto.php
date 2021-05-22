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
 * A name of a variable.
 */
class Name extends Definition
{
    /**
     * @var string
     */
    protected $name;

    public function __construct($name)
    {
        assert('is_string($name)');
        $this->name = $name;
    }

    /**
     * @return  string
     */
    public function __toString()
    {
        return $this->name;
    }
}
