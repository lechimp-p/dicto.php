<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Graph\Node;

// TODO: Maybe this should not extend Entities
class LanguageConstruct extends Entities {
    /**
     * @var string
     */
    private $construct_name;

    public function __construct($construct_name, $name = null) {
        parent::__construct($name);
        assert('is_string($construct_name)');
        // TODO: Restrict the possible construct_names (like @, unset, echo, ...)
        $this->construct_name = $construct_name;     
    }

    /**
     * @return  string
     */
    public function construct_name() {
        return $this->construct_name;
    }

    /**
     * @inheritdoc
     */
    public function meaning() {
        return $this->construct_name();
    }

    /**
     * @inheritdoc
     */
    public function id() {
        return Variable::LANGUAGE_CONSTRUCT_TYPE;
    }

    /**
     * @inheritdocs
     */
    public function compile($negate = false) {
        if (!$negate) {
            return function(Node $n) {
                return $n->type() == Variable::LANGUAGE_CONSTRUCT_TYPE
                    && $n->property("name") == $this->construct_name();
            };
        }
        else {
            return function(Node $n) {
                return $n->type() != Variable::LANGUAGE_CONSTRUCT_TYPE
                    || $n->property("name") != $this->construct_name();
            };
        }
    }
}

