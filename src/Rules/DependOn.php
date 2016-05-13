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
 * A class or function is considered do depend on something if its body
 * of definition makes use of the thing. Language constructs, files or globals
 * can't depend on anything.
 */
class DependOn extends Relation {
    /**
     * @inheritdoc
     */
    public function name() {
        return "depend_on";    
    }

    /**
     * @inheritdoc
     */
    public function compile(Query $query, $rule) {
        $builder = $query->builder();
        $b = $builder->expr();
        $mode = $rule->mode();
        $checked_on = $rule->checked_on();
        $dependency = $rule->right();
        if ($mode == Def\Rules\Rule::MODE_CANNOT || $mode == Def\Rules\Rule::MODE_ONLY_CAN) {
            return $builder
                ->select
                    ( "d.dependent_id as entity_id"
                    , "d.dependency_id as reference_id"
                    , "d.file as file"
                    , "d.line as line"
                    , "d.source_line as source"
                    )
                ->from($query->dependencies_table(), "d")
                ->innerJoin("d", $query->entity_table(), "e", "d.dependent_id = e.id")
                ->innerJoin("d", $query->reference_table(), "r", "d.dependency_id = r.id")
                ->where
                    ( $this->compile_var($b, "e", $checked_on)
                    , $this->compile_var($b, "r", $dependency)
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
                ->leftJoin("e", $query->dependencies_table(), "d", "d.dependent_id = e.id")
                ->leftJoin
                    ("d", $query->reference_table(), "r"
                    , $b->andX
                        ( $b->eq("d.dependency_id", "r.id")
                        , $this->compile_var($b, "r", $dependency)
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
