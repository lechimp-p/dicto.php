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
 * Factory for AST nodes.
 */
class Factory extends Node {
    /**
     * @param   Line[]  $lines
     * @return  Root
     */
    public function root(array $lines) {  
        return new Root($lines);
    }

    /**
     * @param   string  $content
     * @return  Explanation
     */
    public function explanation($content) {
        return new Explanation($content);
    }

    /**
     * @param   string  $name
     * @return  Name
     */
    public function name($name) {
        return new Name($name);
    }

    /**
     * @param   Name        $name
     * @param   Definition  $definition
     * @return  Assignment
     */
    public function assignment(Name $name, Definition $definition) {
        return new Assignment($name, $definition);
    }
}
