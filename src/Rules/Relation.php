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
use Lechimp\Dicto\Analysis\Violation;
use \Lechimp\Dicto\Variables\Variable;

/**
 * This is a rule that checks a relation between two entities
 * in the code.
 */
abstract class Relation extends Schema {
    /**
     * @inheritdoc
     */
    public function fluid_interface(Def\RT $rt, $name, $mode, array $arguments) {
        if (count($arguments) != 0) {
            throw new \InvalidArgumentException(
                "No arguments are allowed when using a relational rule schema.");
        }
        return new Def\Fluid\Relation($rt, $name, $mode, $this);
    }

    public function check_arguments(array $arguments) {
         if (count($arguments) != 1) {
            throw new \InvalidArgumentException(
                "One argument is required when using a relational rule schema.");
        }
       if (!($arguments[0] instanceof Variable)) {
            throw new \InvalidArgumentException(
                "Expected variable, got '".get_class($arguments[0])."' when using a relational schema.");
        }

    }

    /**
     * @inheritdoc
     */
    public function pprint(Rule $rule) {
        return $this->printable_name()." ".$rule->argument(0)->name();
    }

    /**
     * @inheritdoc
     */
    public function compile(Query $query, Rule $rule) {
        $builder = $query->builder();
        $b = $builder->expr();
        $mode = $rule->mode();
        $entity = $rule->checked_on();
        $reference = $rule->argument(0);
        if ($mode == Rule::MODE_CANNOT || $mode == Rule::MODE_ONLY_CAN) {
            return $builder
                ->select
                    ( "rel.entity_id as entity_id"
                    , "rel.reference_id as reference_id"
                    , "rel.file as file"
                    , "rel.line as line"
                    , "src.source as source"
                    )
                ->from($query->relations_table(), "rel")
                ->innerJoin("rel", $query->entity_table(), "e", "rel.entity_id = e.id")
                ->innerJoin("rel", $query->reference_table(), "r", "rel.reference_id = r.id")
                ->innerJoin
                    ( "rel", $query->source_file_table(), "src"
                    , $b->andX
                        ( $b->eq("rel.line", "src.line")
                        , $b->eq("rel.file", "src.name")
                        )
                    )
                ->where
                    ( $b->eq("rel.name", $b->literal($this->name()))
                    , $query->compile_var("e", $entity)
                    , $query->compile_var("r", $reference)
                    )
                ->execute();
        }
        if ($mode == Rule::MODE_MUST) {
            return $builder
                ->select
                    ( "e.id as entity_id"
                    , "e.file as file"
                    , "e.start_line as line"
                    , "src.source as source"
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
                        , $query->compile_var("r", $reference)
                        )
                    )
                ->innerJoin
                    ( "e", $query->source_file_table(), "src"
                    , $b->andX
                        ( $b->eq("e.start_line", "src.line")
                        , $b->eq("e.file", "src.name")
                        )
                    )

                ->where
                    ( $query->compile_var("e", $entity)
                    , $b->isNull("r.id")
                    )
                ->execute();
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }
}
