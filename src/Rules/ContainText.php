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
use Lechimp\Dicto\Graph\PredicateFactory;
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
        $pred_factory = $index->query()->predicate_factory();
        $filter = $rule->checked_on()->compile($pred_factory);
        // ContainText needs to have to kinds of queries, one for files, one
        // for none-files. To make the queries faster, we separate the filters
        // to the different types.
        $filter_non_files = $pred_factory->_and
            ([$pred_factory->_not($pred_factory->_type_is("file"))
            , $filter
            ]);
        $filter_files = $pred_factory->_and
            ([$pred_factory->_type_is("file")
            , $filter
            ]);
        $regexp = $rule->argument(0);

        if ($mode == Rule::MODE_CANNOT || $mode == Rule::MODE_ONLY_CAN) {
            return
                [ $index->query()
                    ->filter($filter_non_files)
                    ->expand_relations(["defined in"])
                    ->filter($this->regexp_source_filter($pred_factory, $regexp, false))
                    ->extract(function($e,&$r) use ($regexp) {
                        $file = $e->target();
                        $found_at_line = $this->get_source_location($e, $regexp);
                        $start_line = $e->property("start_line");
                        $line = $start_line + $found_at_line - 1;
                        // -1 is for the line where the class starts, this would
                        // count double otherwise.
                        $r["file"] = $file->property("path");
                        $r["line"] = $line;
                        $r["source"] = $file->property("source")[$line-1];
                    })
                , $index->query()
                    ->filter($filter_files)
                    ->filter($this->regexp_source_filter($pred_factory, $regexp, false))
                    ->extract(function($e,&$r) use ($regexp) {
                        $line = $this->get_source_location($e, $regexp);
                        $r["file"] = $e->property("path");
                        $r["line"] = $line;
                        $r["source"] = $e->property("source")[$line-1];
                    })
                ];
        }
        if ($mode == Rule::MODE_MUST) {
            return
                [ $index->query()
                    ->filter($filter_non_files)
                    ->expand_relations(["defined in"])
                    ->filter($this->regexp_source_filter($pred_factory, $regexp, true))
                    ->extract(function($e,&$r) use ($rule) {
                        $file = $e->target();
                        $r["file"] = $file->property("path");
                        $line = $e->property("start_line");
                        $r["line"] = $line;
                        $r["source"] = $file->property("source")[$line - 1];
                    })
                // TODO: add implementation for files here.
                ];
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }

    // Helpers for compile

    protected function get_source_for(Graph\Entity $e) {
        if ($e->type() == "defined in") {
            return $this->get_source_for_defined_in($e);
        }
        if ($e->type() == "file") {
            return $this->get_source_for_file($e);
        }
        throw new \LogicException(
            "Can't get source for entity with type '".$e->type()."'");
    }

    protected function get_source_for_defined_in(Graph\Relation $r) {
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

    protected function get_source_for_file(Graph\Node $f) {
        assert('$f->type() == "file"');
        return implode
            ( "\n"
            , $f->property("source")
            );
    }

    protected function regexp_source_filter(PredicateFactory $f, $regexp, $negate) {
        assert('is_string($regexp)');
        assert('is_bool($negate)');
        return $f->_custom(function(Graph\Entity $e) use ($regexp, $negate) {
            $source = $this->get_source_for($e);
            if(!$negate) {
                return preg_match("%$regexp%", $source) == 1;
            }
            else {
                return preg_match("%$regexp%", $source) == 0;
            }
        });
    }

    protected function get_source_location(Graph\Entity $e, $regexp) {
        $matches = [];
        $source = $this->get_source_for($e);
        preg_match("%(.*)$regexp%s", $source, $matches);
        return substr_count($matches[0], "\n") + 1;
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
