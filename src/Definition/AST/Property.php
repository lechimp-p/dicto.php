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
 * A property of some variable.
 */
class Property extends Definition
{
    /**
     * @var Definition
     */
    protected $left;

    /**
     * @var Atom
     */
    protected $id;

    /**
     * @var Parameter[]
     */
    protected $parameters;

    public function __construct(Definition $left, Atom $id, array $parameters)
    {
        $this->left = $left;
        $this->id = $id;
        $this->parameters = array_map(function (Parameter $p) {
            return $p;
        }, $parameters);
    }

    /**
     * @return  Definition
     */
    public function left()
    {
        return $this->left;
    }

    /**
     * @return  Atom
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return  Parameter[]
     */
    public function parameters()
    {
        return $this->parameters;
    }
}
