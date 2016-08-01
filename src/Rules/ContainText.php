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
use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Analysis\Violation;

/**
 * This checks wheather there is some text in the definition of an entity.
 */
class ContainText extends Property {
    /**
     * @inheritdoc
     */
    public function name() {
        return "contain text";
    } 

    /**
     * @inheritdoc
     */
    public function fetch_arguments(ArgumentParser $parser) {
        $regexp = $parser->fetch_string();
        return array($regexp);
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
    public function compile(Query $query, Rule $rule) {
        $builder = $query->builder();
        $b = $builder->expr();
        $mode = $rule->mode();
        $checked_on = $rule->checked_on();
        $regexp = $rule->argument(0);
        if ($mode == Rule::MODE_CANNOT || $mode == Rule::MODE_ONLY_CAN) {
            return $builder
                ->select
                    ( "e.id as entity_id"
                    , "e.file"
                    , "src.source"
                    , "src.line"
                    )
                ->from($query->entity_table(), "e")
                ->innerJoin
                    ( "e", $query->source_file_table(), "src"
                    , $b->andX
                        ( $b->gte("src.line", "e.start_line")
                        , $b->lte("src.line", "e.end_line")
                        , $b->eq("src.name", "e.file")
                        , "src.source REGEXP ?"
                        )
                    )
                ->where
                    ( $checked_on->compile($b, "e")
                    )
                ->setParameter(0, $regexp)
                ->execute();
        }
        if ($mode == Rule::MODE_MUST) {
            return $builder
                ->select
                    ( "e.id as entity_id"
                    , "e.file"
                    , "e.start_line as line"
                    , "src.source"
                    )
                ->from($query->entity_table(), "e")
                ->innerJoin
                    ( "e", $query->source_file_table(), "src"
                    , $b->andX
                        ( $b->eq("src.name", "e.file")
                        , $b->eq("src.line", "e.start_line")
                        )
                    )
                ->leftJoin
                    ( "e", $query->source_file_table(), "match"
                    , $b->andX
                        ( $b->eq("match.name", "e.file")
                        , $b->eq("match.line", "e.start_line")
                        , "match.source REGEXP ?"
                        )
                    )
                ->where
                    ( $checked_on->compile($b, "e")
                    , "match.line IS NULL"
                    )
                ->setParameter(0, $regexp)
                ->execute();
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }

    /**
     * @inheritdoc
     */
    public function pprint(Rule $rule) {
        return $this->name().' "'.$rule->argument(0).'"';
    }
}
