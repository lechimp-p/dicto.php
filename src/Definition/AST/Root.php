<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition\AST;

/**
 * Root node of the AST.
 */
class Root extends Node {
    /**
     * @var Line[]
     */
    protected $lines; 

    public function __construct(array $lines) {
        $this->lines = array_map(function(Line $l) {
            return $l;
        }, $lines);
    }

    /**
     * @return  Line[]
     */
    public function lines() {
        return $this->lines;
    }
}
