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

use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Definition\ArgumentParser;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Indexer\Location;
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
    public function arguments_are_valid(array &$arguments) {
         if (count($arguments) != 1) {
            return false;
        }
        if (!($arguments[0] instanceof Variable)) {
            return false;
        }
        return true;
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
                    ( "rel.name_left as entity_id"
                    , "rel.name_right as reference_id"
                    , "f.path as file"
                    , "rel.line as line"
                    , "src.source as source"
                    )
                ->from($query->relation_table(), "rel")
                ->join
                    ( "rel", $query->file_table(), "f"
                    , $b->eq("rel.file", "f.id")
                    )
                ->join
                    ( "rel", $query->name_table(), "nl"
                    , $b->eq("rel.name_left", "nl.id")
                    )
                ->join
                    ( "rel", $query->name_table(), "nr"
                    , $b->eq("rel.name_right", "nr.id")
                    )
                ->join
                    ( "rel", $query->source_table(), "src"
                    , $b->andX
                        ( $b->eq("src.line", "rel.line")
                        , $b->eq("src.file", "rel.file")
                        )
                    )
                ->where
                    ( $b->eq("rel.which", $b->literal($this->name()))
                    , $entity->compile($b, "nl")
                    , $reference->compile($b, "nr")
                    )
                ->execute();
        }
        if ($mode == Rule::MODE_MUST) {
            return $builder
                ->select
                    ( "d.name as entity_id"
                    , "f.path as file"
                    , "d.start_line as line"
                    , "src.source as source"
                    )
                ->from($query->definition_table(), "d")
                ->join
                    ( "d", $query->file_table(), "f"
                    , $b->eq("d.file", "f.id")
                    )
                ->join
                    ( "d", $query->name_table(), "n"
                    , $b->eq("d.name", "n.id")
                    )
                ->leftJoin
                    ("d", $query->relation_table(), "rel"
                    , $b->andX
                        ( $b->eq("rel.which", $b->literal($this->name()))
                        , $b->eq("rel.name_left", "d.name")
                        )
                    )
                ->innerJoin
                    ( "d", $query->source_table(), "src"
                    , $b->andX
                        ( $b->eq("src.line", "d.start_line")
                        , $b->eq("src.file", "d.file")
                        )
                    )
                ->where
                    ( $entity->compile($b, "n")
                    , $b->isNull("rel.name_right")
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
    protected function insert_relation_into(Insert $insert, Location $location, $ref_id, $line) {
        assert('is_int($ref_id)');
        assert('is_int($line)');
        foreach ($location->in_entities() as $entity) {
            if ($entity[0] == Variable::FILE_TYPE) {
                continue;
            }
            $insert->relation
                ( $this->name()
                , $entity[1]
                , $ref_id
                , $location->file_path()
                , $line
                );
        }
    }
}
