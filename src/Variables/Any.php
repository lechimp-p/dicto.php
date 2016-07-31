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
        return "{".implode(", ", $this->variables)."}";
    }

    /**
     * @inheritdocs
     */
    public function compile(ExpressionBuilder $builder, $table_name, $negate = false) {
        throw new \Exception("NYI!");
    }
}

