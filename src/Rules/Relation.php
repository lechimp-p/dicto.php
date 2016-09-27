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

use Lechimp\Dicto\Definition\ArgumentParser;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Indexer\Location;
use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\Graph\IndexDB;
use Lechimp\Dicto\Graph\Node;

/**
 * This is a rule that checks a relation between two entities
 * in the code.
 *
 * TODO: Test if relation can be used for files.
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
    public function compile(IndexDB $index, Rule $rule) {
        $mode = $rule->mode();
        $var_left = $rule->checked_on();
        $var_right = $rule->argument(0);
        if ($mode == Rule::MODE_CANNOT || $mode == Rule::MODE_ONLY_CAN) {
            $filter_left = $var_left->compile();
            $filter_right = $var_right->compile();
            return $index->query()
                ->filter($filter_left)
                ->expand_relation([$this->name()])
                ->extract(function($e,&$r) use ($index, $rule) {
                    $file = $e->property("file");
                    assert('$file->type() == "file"');
                    $r["rule"] = $rule;
                    $r["file"] = $file->property("path");
                    $line = $e->property("line");
                    $r["line"] = $line;
                    $r["source"] = $file->property("source")[$line - 1];
                })
                ->expand_target()
                ->filter($filter_right)
                ;
        }
        if ($mode == Rule::MODE_MUST) {
            $filter_left = $var_left->compile();
            $filter_right = $var_right->compile();
            return $index->query()
                ->filter($filter_left)
                ->filter(function(Node $n) use ($filter_right) {
                    $rels = $n->relations(function($r) {
                        return $r->type() == $this->name();
                    });
                    if (count($rels) == 0) {
                        return true;
                    }
                    foreach ($rels as $rel) {
                        if ($filter_right($rel->target())) {
                            return false;
                        }
                    }
                    return true;
                })
                ->extract(function($e,&$r) use ($index, $rule) {
                    print_r($e);
                    $rels = $e->relations(function($r) {
                        return $r->type() == "defined in";
                    });
                    assert('count($rels) == 1');
                    $file = $rels[0]->target();
                    assert('$file->type() == "file"');
                    $r["rule"] = $rule;
                    $r["file"] = $file->property("path");
                    $line = $rels[0]->property("start_line");
                    $r["line"] = $line;
                    $r["source"] = $file->property("source")[$line - 1];
                })
                ;
        }
        throw new \LogicException("Unknown rule mode: '$mode'");
    }

    /**
     * Insert this relation somewhere, where it is recorded for all
     * entities that the current location is in.
     *
     * @param   Insert      $insert
     * @param   Location    $location
     * @param   mixed       $other
     * @return  null
     */
    protected function insert_relation_into(Insert $insert, Location $location, $other, $line) {
        assert('is_int($line)');
        foreach ($location->in_entities() as $entity) {
            if ($entity[0] == Variable::FILE_TYPE) {
                continue;
            }
            $insert->_relation
                ( $entity[1]
                , $this->name()
                , $other
                , $location->file()
                , $line
                );
        }
    }
}
