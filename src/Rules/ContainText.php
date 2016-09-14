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
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Definition\ArgumentParser;
use Lechimp\Dicto\Indexer\ListenerRegistry;

/**
 * This checks wheather there is some text in some entity.
 *
 * TODO: Test if ContainText finds text in files.
 */
class ContainText extends Schema {
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
    public function arguments_are_valid(array &$arguments) {
        if (count($arguments) != 1) {
            return false;
        }
        $regexp = $arguments[0];
        if (!is_string($regexp) || @preg_match("%$regexp%", "") === false) {
            return false;
        }
        return true;
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
                    ( "d.name"
                    , "f.path as file"
                    , "src.line"
                    , "src.source"
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
                // TODO: This is a dirty hack, since i always join the method
                // info table without knowing if the thing is a method.
                ->leftJoin
                    ( "d", $query->method_info_table(), "mi"
                    , $b->eq("d.name", "mi.name")
                    )
                // END HACK
                ->join
                    ( "d", $query->source_table(), "src"
                    , $b->andX
                        ( $b->gte("src.line", "d.start_line")
                        , $b->lte("src.line", "d.end_line")
                        , $b->eq("src.file", "d.file")
                        , "src.source REGEXP ?"
                        )
                    )
                ->where
                    ( $checked_on->compile($b, "n")
                    )
                ->setParameter(0, $regexp)
                ->execute();
        }
        if ($mode == Rule::MODE_MUST) {
            return $builder
                ->select
                    ( "d.name"
                    , "f.path as file"
                    , "d.start_line as line"
                    , "src.source"
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
                // TODO: This is a dirty hack, since i always join the method
                // info table without knowing if the thing is a method.
                ->leftJoin
                    ( "d", $query->method_info_table(), "mi"
                    , $b->eq("d.name", "mi.name")
                    )
                // END HACK
                ->join
                    ( "d", $query->source_table(), "src"
                    , $b->andX
                        ( $b->eq("src.file", "d.file")
                        , $b->eq("src.line", "d.start_line")
                        )
                    )
                ->leftJoin
                    ( "d", $query->source_table(), "match"
                    , $b->andX
                        ( $b->eq("match.file", "d.file")
                        , $b->eq("match.line", "d.start_line")
                        , "match.source REGEXP ?"
                        )
                    )
                ->where
                    ( $checked_on->compile($b, "n")
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

    /**
     * No listeners required for contains text. 
     *
     * @inheritdoc
     */
    public function register_listeners(ListenerRegistry $registry) {
    }

}
