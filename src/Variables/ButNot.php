<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

class ButNot extends Compound {
    /**
     * @inheritdocs
     */
    public function compile(ExpressionBuilder $builder, $table_name, $negate = false) {
        if ($negate) {
            throw \LogicException("NYI!");
        }
        return $builder->andX
            ( $this->left()->compile($builder, $table_name)
            , $this->right()->compile($builder, $table_name, true)
            );
    }
}
