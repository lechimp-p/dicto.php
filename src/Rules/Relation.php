<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Rules;

use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Indexer\Location;
use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Variables\Variable;

/**
 * This is a rule that checks a relation between two entities
 * in the code.
 */
abstract class Relation extends Schema {
    /**
     * @inheritdoc
     */
    public function fetch_arguments(ArgumentParser $parser) {
        $var = $parser->fetch_variable();
        return array($var);
    }

    /**
     * @inheritdoc
     */
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
        return $this->name()." ".$rule->argument(0)->name();
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
                    , "r.file as file"
                    , "r.line as line"
                    , "src.source as source"
                    )
                ->from($query->relations_table(), "rel")
                ->innerJoin("rel", $query->entity_table(), "e", "rel.entity_id = e.id")
                ->innerJoin("rel", $query->reference_table(), "r", "rel.reference_id = r.id")
                ->innerJoin
                    ( "rel", $query->source_file_table(), "src"
                    , $b->andX
                        ( $b->eq("src.line", "r.line")
                        , $b->eq("src.name", "r.file")
                        )
                    )
                ->where
                    ( $b->eq("rel.name", $b->literal($this->name()))
                    , $entity->compile($b, "e")
                    , $reference->compile($b, "r")
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
                        , $reference->compile($b, "r")
                        )
                    )
                ->innerJoin
                    ( "e", $query->source_file_table(), "src"
                    , $b->andX
                        ( $b->eq("src.line", "e.start_line")
                        , $b->eq("src.name", "e.file")
                        )
                    )

                ->where
                    ( $entity->compile($b, "e")
                    , $b->isNull("r.id")
                    )
                ->execute();
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }

    /**
     * Insert this relation somewhere, where it is recorded for all
     * entities that the current location is in, except for files.
     *
     * @param   Insert      $insert
     * @param   Location    $location
     * @param   int         $ref_id
     * @return  null
     */
    protected function insert_relation_into(Insert $insert, Location $location, $ref_id) {
        assert('is_int($ref_id)');
        foreach ($location->in_entities() as $entity) {
            if ($entity[0] == Variable::FILE_TYPE) {
                continue;
            }
            $insert->relation
                ( $this->name()
                , $entity[1]
                , $ref_id
                );
        }
    }
}
