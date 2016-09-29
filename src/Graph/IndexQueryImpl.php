<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

/**
 * A query on the IndexDB.
 */
class IndexQueryImpl extends QueryImpl implements IndexQuery {
    public function filter_by_types($types) {
        assert('is_array($types)');
        return $this->filter(function(Node $n) use ($types) {
            return in_array($n->type(), $types);
        });
    }

    /**
     */
    public function files() {
        return $this->filter_by_types(["file"]);
    }

    /**
     * @inheritdocs
     */
    public function classes() {
        return $this->filter_by_types(["class"]);
    }

    /**
     * @inheritdocs
     */
    public function methods() {
        return $this->filter_by_types(["method"]);
    }

    /**
     * @inheritdocs
     */
    public function functions() {
        return $this->filter_by_types(["function"]);
    }

    /**
     * @inheritdocs
     */
    public function expand_relation(array $types) {
        return $this->expand(function(Node $n) use (&$types) {
            return array_filter
                ( $n->relations()
                , function(Relation $r) use (&$types) {
                    return in_array($r->type(), $types);
                });
        });
    }

    /**
     * @inheritdocs
     */
    public function expand_target() {
        return $this->expand(function(Relation $r) {
            return [$r->target()];
        });
    }
}
