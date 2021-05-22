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
 * Just a string...
 */
class StringValue extends Parameter
{
    /**
     * @var string
     */
    protected $string;

    public function __construct($string)
    {
        assert('is_string($string)');
        $this->string = $string;
    }

    /**
     * @return  string
     */
    public function __toString()
    {
        return $this->string;
    }
}
