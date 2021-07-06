<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Rules;

use Lechimp\Regexp\Regexp;
use Lechimp\Dicto\Analysis\Index;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Definition\ArgumentParser;
use Lechimp\Dicto\Graph\Node;
use Lechimp\Dicto\Graph\PredicateFactory;
use Lechimp\Dicto\Graph;

/**
 * This checks wheather there is some text in some entity.
 */
class ContainText extends Schema
{
    /**
     * @inheritdoc
     */
    public function name()
    {
        return "contain text";
    }

    /**
     * @inheritdoc
     */
    public function fetch_arguments(ArgumentParser $parser)
    {
        $regexp = new Regexp($parser->fetch_string());
        return array($regexp);
    }

    /**
     * @inheritdoc
     */
    public function arguments_are_valid(array &$arguments)
    {
        if (count($arguments) != 1 || !($arguments[0] instanceof Regexp)) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function compile(Index $index, Rule $rule)
    {
        $mode = $rule->mode();
        $pred_factory = $index->query()->predicate_factory();
        $filter = $rule->checked_on()->compile($pred_factory);
        // ContainText needs to have to kinds of queries, one for files, one
        // for none-files. To make the queries faster, we separate the filters
        // to the different types.
        $filter_non_files = $pred_factory->_and([$pred_factory->_not($pred_factory->_type_is("file"))
            , $filter
            ]);
        $filter_files = $pred_factory->_and([$pred_factory->_type_is("file")
            , $filter
            ]);
        $regexp = $rule->argument(0);
        // TODO: This behaviour might better be encoded as a method on regexp.
        //       This is needed in get_source_location to find the number of
        //       the line the regexp was found.
        $loc_regexp = new Regexp(".*" . $rule->argument(0)->raw());

        if ($mode == Rule::MODE_CANNOT || $mode == Rule::MODE_ONLY_CAN) {
            return
                [ $index->query()
                    ->filter($filter_non_files)
                    ->expand_relations(["defined in"])
                    ->filter($this->regexp_source_filter($pred_factory, $regexp, false))
                    ->extract(function ($e, &$r) use ($regexp, $loc_regexp) {
                        $file = $e->target();
                        $found_at_line = $this->get_source_location($e, $loc_regexp);
                        $start_line = $e->property("start_line");
                        $line = $start_line + $found_at_line - 1;
                        // -1 is for the line where the class starts, this would
                        // count double otherwise.
                        $r["file"] = $file->property("path");
                        $r["line"] = $line;
                        $r["source"] = $file->property("source")[$line - 1];
                    })
                , $index->query()
                    ->filter($filter_files)
                    ->filter($this->regexp_source_filter($pred_factory, $regexp, false))
                    ->extract(function ($e, &$r) use ($regexp, $loc_regexp) {
                        $line = $this->get_source_location($e, $loc_regexp);
                        $r["file"] = $e->property("path");
                        $r["line"] = $line;
                        $r["source"] = $e->property("source")[$line - 1];
                    })
                ];
        }
        if ($mode == Rule::MODE_MUST) {
            return
                [ $index->query()
                    ->filter($filter_non_files)
                    ->expand_relations(["defined in"])
                    ->filter($this->regexp_source_filter($pred_factory, $regexp, true))
                    ->extract(function ($e, &$r) use ($rule) {
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

    protected function get_source_for(Graph\Entity $e)
    {
        if ($e->type() == "defined in") {
            return $this->get_source_for_defined_in($e);
        }
        if ($e->type() == "file") {
            return $this->get_source_for_file($e);
        }
        throw new \LogicException(
            "Can't get source for entity with type '" . $e->type() . "'"
        );
    }

    protected function get_source_for_defined_in(Graph\Relation $r)
    {
        assert('$r->type() == "defined in"');
        $start_line = $r->property("start_line");
        $end_line = $r->property("end_line");
        return implode(
            "\n",
            array_slice(
                    $r->target()->property("source"),
                    $start_line - 1,
                    $end_line - $start_line
                )
        );
    }

    protected function get_source_for_file(Graph\Node $f)
    {
        assert('$f->type() == "file"');
        return implode(
            "\n",
            $f->property("source")
        );
    }

    protected function regexp_source_filter(PredicateFactory $f, Regexp $regexp, $negate)
    {
        assert('is_bool($negate)');
        return $f->_custom(function (Graph\Entity $e) use ($regexp, $negate) {
            $source = $this->get_source_for($e);
            if (!$negate) {
                return $regexp->search($source);
            } else {
                return !$regexp->search($source);
            }
        });
    }

    protected function get_source_location(Graph\Entity $e, Regexp $regexp)
    {
        $matches = [];
        $source = $this->get_source_for($e);
        $regexp->search($source, true, $matches);
        return substr_count($matches[0], "\n") + 1;
    }

    /**
     * @inheritdoc
     */
    public function pprint(Rule $rule)
    {
        return $this->name() . ' "' . $rule->argument(0)->raw() . '"';
    }
}
