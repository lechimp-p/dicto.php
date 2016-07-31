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

/**
 * Another variable that has a certain name.
 */
class WithName extends Variable {
    /**
     * @var string
     */
    private $regexp;

    /**
     * @var Variable
     */
    private $other;

    public function __construct($regexp, Variable $other) {
        // TODO: call parent constructur without name? (see BACKLOG)
        parent::__construct($other->name());
        if (!is_string($regexp) || @preg_match("%$regexp%", "") === false) {
            throw new \InvalidArgumentException("Invalid regexp: '%$regexp%'");
        }
        $this->regexp = $regexp;
        $this->other = $other;
    }

    /**
     * @return  string
     */
    public function regexp() {
        return $this->regexp;
    }

    /**
     * @return  Variable
     */
    public function variable() {
        return $this->other;
    }

    /**
     * @inheritdocs
     */
    public function meaning() {
        $re = $this->regexp();
        return $this->variable()->meaning()." with name \"$re\""; 
    }

    /**
     * @inheritdocs
     */
    public function compile(ExpressionBuilder $builder, $table_name, $negate = false) {
        // normal case : left_condition AND regexp matches
        if (!$negate) {
            return $builder->andX
                ( $this->variable()->compile($builder, $table_name)
                , "$table_name.name REGEXP ".$builder->literal('^'.$this->regexp().'$')
                );
        }
        // negated case: not (left_condition_left and regexp matches)
        //             = not left_condition and not regexp matches
        else {
            return $builder->orX
                ( $this->variable()->compile($builder, $table_name, true)
                , "$table_name.name NOT REGEXP ".$builder->literal('^'.$this->regexp().'$')
                );
        }
    }
}
