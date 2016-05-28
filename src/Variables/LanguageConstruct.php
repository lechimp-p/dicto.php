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

class LanguageConstruct extends Entities {
    /**
     * @var string
     */
    private $construct_name;

    public function __construct($name, $construct_name) {
        parent::__construct($name);
        assert('is_string($construct_name)');
        // TODO: Restrict the possible construct_names (like @, unset, echo, ...)
        $this->construct_name = $construct_name;     
    }

    /**
     * @return  string
     */
    public function construct_name() {
        return $this->construct_name;
    }

    /**
     * @inheritdoc
     */
    static public function id() {
        return Variable::LANGUAGE_CONSTRUCT_TYPE;
    }

    /**
     * @inheritdocs
     */
    public function compile(ExpressionBuilder $builder, $table_name, $negate = false) {
        $type_expr = $this->eq_op
            ( $builder
            , "$table_name.type"
            , $builder->literal(Variable::LANGUAGE_CONSTRUCT_TYPE)
            , $negate
            );
        $name_expr = $this->eq_op
            ( $builder
            , "$table_name.name"
            , $builder->literal($this->construct_name())
            , $negate
            );

        // normal case : language construct and name matches
        if (!$negate) {
            return $builder->andX($type_expr, $name_expr);
        }
        // negated case: not (language construct and name matches)
        //             = not language construct or not name matches
        else {
            return $builder->orX($type_expr, $name_expr);
        }
    }
}

