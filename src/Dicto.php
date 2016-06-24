<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto;

class Dicto {
    private function __construct() {}

    static private $rt = null;

    /**
     * Discard the current definition if there is any.
     */
    public static function discardDefinition() {
        self::$rt = null; 
    }

    /**
     * Start the definition of a new ruleset.
     *
     * @throws  \RuntimeException   if definition was already started
     */
    public static function startDefinition() {
        if (self::$rt !== null) {
            throw new \RuntimeException("Already started a rule definition");
        }
        self::$rt = new Definition\RT();
    }

    /**
     * Discard the current definition if there is any.
     *
     * @throws  \RuntimeException   if definition was not started or already ended
     * @return  array   containing Definition\RuleSet and config-array
     */
    public static function endDefinition() {
        if (self::$rt === null) {
            throw new \RuntimeException("Already ended or not even started the rule definition");
        }
        $rule_set = self::$rt->ruleset();
        $config = self::$rt->configuration();
        self::discardDefinition();
        return array($rule_set, $config ? $config : array());
    }

    /**
     * Define a only-rule.
     *
     * @throws  \RuntimeException   if previous variable declaration has not finished
     * @throws  \RuntimeException
     * @return  Fluid\Only
     */
    public static function only() {
        if (self::$rt === null) {
            throw new \RuntimeException(
                "No variable definition allowed outside ruleset definition.");
        }
        return self::$rt->only();
    }

    /**
     * Define the configuration for the project according to App\Config.
     *
     * @param   array
     * @return  null
     */
    public static function configuration(array $config) {
        self::$rt->configuration($config);
    }

    /**
     * Define a new variable or reference an already defined variable to define
     * a rule.
     *
     * @throws  \InvalidArgumentException   if $arguments are passed
     * @throws  \RuntimeException           if previous variable declaration was not finished
     * @throws  \RuntimeException           if definition was not started 
     * @return  NewVar|RuleVar
     */
    public static function __callStatic($name, $arguments) {
        # ToDo: This is used in Definition\Fluid\Means as well.
        if (count($arguments) != 0) {
            throw new \InvalidArgumentException(
                "No arguments are allowed for definition of ".
                "or reference to variable.");
        }
        if (self::$rt === null) {
            throw new \RuntimeException(
                "No variable definition allowed outside ruleset definition.");
        }
        return self::$rt->variable($name);
    }
}

