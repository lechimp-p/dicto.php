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

abstract class Entities extends Variable {
    public function __construct($name= null) {
        if ($name === null) {
            $name = ucfirst($this->id());
        }
        parent::__construct($name);
    }

    /**
     * Get an id for the type of entity.
     *
     * This must return a string without whitespaces.
     *
     * @return  string
     */
    abstract public function id();

    /**
     * @inheritdocs
     */
    public function meaning() {
        return $this->id();
    }

    /**
     * @inheritdocs
     */
    public function compile(ExpressionBuilder $builder, $name_table_name, $method_info_table_name, $negate = false) {
        return $this->eq_op
            ( $builder
            , "$name_table_name.type"
            , $builder->literal($this->id())
            , $negate);
    }

    protected function eq_op(ExpressionBuilder $builder, $l, $r, $negate) {
        if (!$negate) {
            return $builder->eq($l, $r);
        }
        else {
            return $builder->neq($l, $r);
        }
    }
}

