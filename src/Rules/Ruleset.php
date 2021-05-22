<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Rules;

use Lechimp\Dicto\Variables\Variable;

/**
 * A set of rules and variable definitions.
 */
class Ruleset
{
    /**
     * @var Variables\Variable[]
     */
    private $variables;

    /**
     * @var Rule[]  $rules
     */
    private $rules;

    public function __construct(array $variables, array $rules)
    {
        $this->variables = array_map(function (Variable $v) {
            return $v;
        }, $variables);

        $this->rules = array_map(function (Rule $r) {
            return $r;
        }, $rules);
    }

    /**
     * @return  Variables\Variable[]
     */
    public function variables()
    {
        return $this->variables;
    }

    /**
     * @return  Rule[]
     */
    public function rules()
    {
        return $this->rules;
    }
}
