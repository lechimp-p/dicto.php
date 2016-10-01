<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Graph\Node;
use Lechimp\Dicto\Graph\Relation;
use Lechimp\Dicto\Definition\ArgumentParser;

/**
 * Name is a property, right?
 */
class In extends Property {
    static private $relations = ["contained in"];

    /**
     * @inheritdocs
     */
    public function name() {
        return "in";
    }

    /**
     * @inheritdocs
     */
    public function parse_as() {
        return $this->name();
    }

    /**
     * @inheritdocs
     */
    public function fetch_arguments(ArgumentParser $parser) {
        $other = $parser->fetch_variable();
        return array($other);
    }

    /**
     * @inheritdocs
     */
    public function arguments_are_valid(array &$arguments) {
        if (count($arguments) != 1) {
            return false;
        }
        return $arguments[0] instanceof Variable;
    }

    /**
     * @inheritdocs
     */
    public function compile(array &$arguments) {
        $condition = $arguments[0]->compile();
        return function (Node $n) use ($condition) {
            $nodes = $n->related_nodes(function (Relation $r) use ($condition) {
                return in_array($r->type(), self::$relations);
            });
            foreach ($nodes as $node) {
                if ($condition($node)) {
                    return true;
                }
            }
            return false;
        };
    }
}
