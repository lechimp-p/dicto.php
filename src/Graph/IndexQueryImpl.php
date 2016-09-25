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
    protected function type_filter($type) {
        return $this->filter(function(Node $n) use ($type) {
            return $n->type() == $type;
        });
    }

    /**
     */
    public function files() {
        return $this->type_filter("file");
    }

    /**
     * @inheritdocs
     */
    public function classes() {
        return $this->type_filter("class");
    }

    /**
     * @inheritdocs
     */
    public function methods() {
        return $this->type_filter("method");
    }

    /**
     * @inheritdocs
     */
    public function functions() {
        return $this->type_filter("function");
    }

    /**
     * @inheritdocs
     */
    public function globals() {
        return $this->type_filter("global");
    }

    /**
     * @inheritdocs
     */
    public function language_constructs() {
        return $this->type_filter("language construct");
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
