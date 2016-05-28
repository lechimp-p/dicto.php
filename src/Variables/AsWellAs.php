<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

class AsWellAs extends Compound {
    /**
     * @inheritdocs
     */
    public function compile(ExpressionBuilder $builder, $table_name, $negate = false) {
        // normal case: left_condition or right_condition
        if (!$negate) {
            return $builder->orX
                ( $this->left()->compile($builder, $table_name)
                , $this->right()->compile($builder, $table_name)
                );
        }
        // negated case: not (left_condition or right_condition)
        //             = not left_condition and not right_condition
        if ($negate) {
            return $builder->andX
                ( $this->left()->compile($builder, $table_name, true)
                , $this->right()->compile($builder, $table_name, true)
                );
        }
    }

}
