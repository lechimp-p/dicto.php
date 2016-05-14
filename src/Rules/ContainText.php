<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Rules;

use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Analysis\Query;

/**
 * This checks wheather there is some text in the definition of an entity.
 */
class ContainText extends Property {
    /**
     * @inheritdoc
     */
    public function name() {
        return "contain_text";
    } 

    /**
     * @inheritdoc
     */
    public function check_arguments(array $arguments) {
        if (count($arguments) != 1) {
            throw new \InvalidArgumentException(
                "One argument is required when using a contain text.");
        }
        $regexp = $arguments[0];
        if (!is_string($regexp) || @preg_match("%$regexp%", "") === false) {
            throw new \InvalidArgumentException(
                "Invalid regexp '$regexp' when using contain text.");
        }
    }

    /**
     * @inheritdoc
     */
    public function compile(Query $query, $rule) {
        $builder = $query->builder();
        $mode = $rule->mode();
        $checked_on = $rule->checked_on();
        $regexp = $rule->argument(0);
        if ($mode == Def\Rules\Rule::MODE_CANNOT || $mode == Def\Rules\Rule::MODE_ONLY_CAN) {
            return $builder
                ->select
                    ( "id as entity_id"
                    , "file"
                    , "start_line as line"
                    , "source"
                    )
                ->from($query->entity_table())
                ->where
                    ( $this->compile_var($builder->expr(), $query->entity_table(), $checked_on)
                    , "source REGEXP ?"
                    )
                ->setParameter(0, $regexp)
                ->execute();
        }
        if ($mode == Def\Rules\Rule::MODE_MUST) {
            return $builder
                ->select
                    ( "id as entity_id"
                    , "file"
                    , "start_line as line"
                    , "source"
                    )
                ->from($query->entity_table())
                ->where
                    ( $this->compile_var($builder->expr(), $query->entity_table(), $checked_on)
                    , "source NOT REGEXP ?"
                    )
                ->setParameter(0, $regexp)
                ->execute();
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }

    /**
     * @inheritdoc
     */
    public function pprint($rule) {
        return $this->printable_name().' "'.$rule->argument(0).'"';
    }
}
