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

use Lechimp\Dicto\Analysis\Index;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Definition\ArgumentParser;
use Lechimp\Dicto\Indexer\ListenerRegistry;
use Lechimp\Dicto\Graph\Node;
use Lechimp\Dicto\Graph;

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
    public function compile(Index $index, Rule $rule) {
        $mode = $rule->mode();
        $filter = $rule->checked_on()->compile();
        $regexp = $rule->argument(0);
        $regexp_filter = function(Graph\Relation $r) use ($regexp) {
            $start_line = $r->property("start_line");
            $end_line = $r->property("end_line");
            $source = implode
                ( "\n"
                , array_slice
                    ( $r->target()->property("source")
                    , $start_line - 1
                    , $end_line - $start_line
                    )
                );
            return preg_match("%$regexp%", $source) == 1;
        };

        if ($mode == Rule::MODE_CANNOT || $mode == Rule::MODE_ONLY_CAN) {
            return $index->query()
                ->filter($filter)
                ->expand_relations(["defined in"])
                ->filter($this->regexp_source_filter($regexp, false))
                ->extract(function($e,&$r) use ($rule, $regexp) {
                    $matches = [];
                    $source = $this->get_source_for($e);
                    preg_match("%(.*)$regexp%", $source, $matches);

                    $file = $e->target();
                    $r["file"] = $file->property("path");
                    $start_line = $e->property("start_line");
                    $found_at_line = substr_count($matches[0], "\n") + 1;
                    $line = $start_line + $found_at_line;
                    $r["line"] = $line + 1;
                    $r["source"] = $file->property("source")[$line];
                });
        }
        if ($mode == Rule::MODE_MUST) {
            return $index->query()
                ->filter($filter)
                ->expand_relations(["defined in"])
                ->filter($this->regexp_source_filter($regexp, true))
                ->extract(function($e,&$r) use ($rule) {
                    $file = $e->target();
                    $r["file"] = $file->property("path");
                    $line = $e->property("start_line");
                    $r["line"] = $line;
                    $r["source"] = $file->property("source")[$line - 1];
                });
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }

    // Helpers for compile

    protected function get_source_for(Graph\Relation $r) {
        assert('$r->type() == "defined in"');
        $start_line = $r->property("start_line");
        $end_line = $r->property("end_line");
        return implode
            ( "\n"
            , array_slice
                ( $r->target()->property("source")
                , $start_line - 1
                , $end_line - $start_line
                )
            );
    }

    protected function regexp_source_filter($regexp, $negate) {
        assert('is_string($regexp)');
        assert('is_bool($negate)');
        return function(Graph\Relation $r) use ($regexp, $negate) {
            $source = $this->get_source_for($r);
            if(!$negate) {
                return preg_match("%$regexp%", $source) == 1;
            }
            else {
                return preg_match("%$regexp%", $source) == 0;
            }
        };
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
