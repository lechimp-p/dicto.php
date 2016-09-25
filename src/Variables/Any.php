<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

use Lechimp\Dicto\Graph\Node;

/**
 * Variable matching any of the sub variables.
 */
class Any extends Variable {
    /**
     * @var Variable[]
     */
    protected $variables;

    public function __construct(array $variables) {
        parent::__construct();
        $this->variables = array_map(function(Variable $v) { return $v; }, $variables);
    }

    /**
     * @inheritdocs
     */
    public function meaning() {
        $meanings = array_map(function($v) { return $v->meaning(); }, $this->variables);
        return "{".implode(", ", $meanings)."}";
    }

    /**
     * @inheritdocs
     */
    public function compile($negate = false) {
        $conditions = array_map(function(Variable $v) use ($negate) {
            return $v->compile($negate);
        }, $this->variables);

        // normal case: 1 or 2 or 3 ...
        if (!$negate) {
            return function(Node $n) use (&$conditions) {
                foreach ($conditions as $condition) {
                    if ($condition($n)) {
                        return true;
                    }
                }
                return false;
            };
        }
        // negated case: not (left_condition or right_condition)
        //             = not left_condition and not right_condition
        if ($negate) {
            return function(Node $n) use (&$conditions) {
                foreach ($conditions as $condition) {
                    if (!$condition($n)) {
                        return false;
                    }
                }
                return true;
            };
        }
    }
}

