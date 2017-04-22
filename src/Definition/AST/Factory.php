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
     * @param   string  $atom
     * @return  Name
     */
    public function atom($atom) {
        return new Atom($atom);
    }

    /**
     * @param   string  $string
     * @return  StringValue
     */
    public function string_value($string) {
        return new StringValue($string);
    }

    /**
     * @param   Definition  $left
     * @param   Atom        $id
     * @param   Parameter[] $parameters
     * @return  Property
     */
    public function property(Definition $left, Atom $id, array $parameters) {
        return new Property($left, $id, $parameters);
    }

    /**
     * @param   Definition  $left
     * @param   Definition  $right
     * @return  Except
     */
    public function except(Definition $left, Definition $right) {
        return new Except($left, $right);
    }

    /**
     * @param   Definition[] $definitions
     * @return  Any
     * @return  Any
     */
    public function any(array $definitions) {
        return new Any($definitions);
    }

    /**
     * @param   Name        $name
     * @param   Definition  $definition
     * @return  Assignment
     */
    public function assignment(Name $name, Definition $definition) {
        return new Assignment($name, $definition);
    }

    /**
     * @return  Qualifier
     */
    public function must() {
        return new Qualifier(Qualifier::MUST);
    }

    /**
     * @return  Qualifier
     */
    public function cannot() {
        return new Qualifier(Qualifier::CANNOT);
    }

    /**
     * @return  Qualifier
     */
    public function only_X_can() {
        return new Qualifier(Qualifier::ONLY_X_CAN);
    }

    /**
     * @param   Definition  $left
     * @param   Qualifier   $qualifier
     * @param   Atom        $id
     * @param   Parameter[] $parameters
     * @return  Property
     */
    public function rule(Definition $left, Qualifier $qualifier, Atom $id, array $parameters) {
        return new Rule($left, $qualifier, $id, $parameters);
    }
}
