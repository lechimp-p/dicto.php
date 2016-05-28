<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

abstract class Entities extends Variable {
    /**
     * Identifier for the type.
     *
     * @return  string
     */
    abstract public function id();

    /**
     * @inheritdocs
     */
    public function compile(ExpressionBuilder $builder, $table_name, $negate = false) {
        return $this->eq_op
            ( $builder
            , "$table_name.type"
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

