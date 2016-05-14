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
 * This is a rule that checks a relation between two entities
 * in the code.
 */
abstract class Relation extends Schema {
    /**
     * @inheritdoc
     */
    public function fluid_interface(Def\RuleDefinitionRT $rt, $name, $mode) {
        return new Def\Fluid\Relation($rt, $name, $mode, $this);
    }

    /**
     * @inheritdoc
     */
    public function pprint($rule) {
        assert('$rule instanceof \\Lechimp\\Dicto\\Definition\\Rules\\Relation');
        return $this->printable_name()." ".$rule->right()->name();
    }

    /**
     * @inheritdoc
     */
    public function compile(Query $query, $rule) {
        $builder = $query->builder();
        $b = $builder->expr();
        $mode = $rule->mode();
        $entity = $rule->checked_on();
        $reference = $rule->right();
        if ($mode == Def\Rules\Rule::MODE_CANNOT || $mode == Def\Rules\Rule::MODE_ONLY_CAN) {
            return $builder
                ->select
                    ( "rel.entity_id as entity_id"
                    , "rel.reference_id as reference_id"
                    , "rel.file as file"
                    , "rel.line as line"
                    , "rel.source_line as source"
                    )
                ->from($query->relations_table(), "rel")
                ->innerJoin("rel", $query->entity_table(), "e", "rel.entity_id = e.id")
                ->innerJoin("rel", $query->reference_table(), "r", "rel.reference_id = r.id")
                ->where
                    ( $b->eq("rel.name", $b->literal($this->name()))
                    , $this->compile_var($b, "e", $entity)
                    , $this->compile_var($b, "r", $reference)
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
                ->leftJoin
                    ("e", $query->relations_table(), "rel"
                    , $b->andX
                        ( $b->eq("rel.name", $b->literal($this->name()))
                        , $b->eq("rel.entity_id", "e.id")
                        )
                    )
                ->leftJoin
                    ("rel", $query->reference_table(), "r"
                    , $b->andX
                        ( $b->eq("rel.reference_id", "r.id")
                        , $this->compile_var($b, "r", $reference)
                        )
                    )
                ->where
                    ( $this->compile_var($b, "e", $entity)
                    , $b->isNull("r.id")
                    )
                ->execute();
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }

}
