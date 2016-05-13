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

class Invoke extends Relation {
    /**
     * @inheritdoc
     */
    public function name() {
        return "invoke";    
    }

    /**
     * @inheritdoc
     */
    public function compile(Query $query, $rule) {
        $builder = $query->builder();
        $b = $builder->expr();
        $mode = $rule->mode();
        $checked_on = $rule->checked_on();
        $invokee = $rule->right();
        if ($mode == Def\Rules\Rule::MODE_CANNOT || $mode == Def\Rules\Rule::MODE_ONLY_CAN) {
            return $builder
                ->select
                    ( "i.invoker_id as entity_id"
                    , "i.invokee_id as reference_id"
                    , "i.file as file"
                    , "i.line as line"
                    , "i.source_line as source"
                    )
                ->from($query->invocations_table(), "i")

                ->innerJoin("i", $query->entity_table(), "e", "i.invoker_id = e.id")
                ->innerJoin("i", $query->reference_table(), "r", "i.invokee_id = r.id")
                ->where
                    ( $this->compile_var($b, "e", $checked_on)
                    , $this->compile_var($b, "r", $invokee)
                    )
                ->execute();
        }
        if ($mode == Def\Rules\Rule::MODE_MUST) {
            return $builder
                ->select
                    ( "e.id as entity_id"
                    , "e.file as file"
                    , "e.start_line as line"
                    , "e.source as source"
                    )
                ->from($query->entity_table(), "e")
                ->leftJoin("e", $query->invocations_table(), "i", "i.invoker_id = e.id")
                ->leftJoin
                    ( "i", $query->reference_table(), "r"
                    , $b->andX
                        ( $b->eq("i.invokee_id", "r.id")
                        , $this->compile_var($b, "r", $invokee)
                        )
                    )
                ->where
                    ( $this->compile_var($b, "e", $checked_on)
                    , $b->isNull("r.id")
                    )
                ->execute();
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }
}
