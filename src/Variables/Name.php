<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Regexp;
use Lechimp\Dicto\Graph\PredicateFactory;
use Lechimp\Dicto\Definition\ArgumentParser;

/**
 * Name is a property, right?
 */
class Name extends Property {
    /**
     * @inheritdocs
     */
    public function name() {
        return "name";
    }

    /**
     * @inheritdocs
     */
    public function fetch_arguments(ArgumentParser $parser) {
        $regexp = new Regexp($parser->fetch_string());
        return array($regexp);
    }

    /**
     * @inheritdocs
     */
    public function arguments_are_valid(array &$arguments) {
        if (count($arguments) != 1 || !($arguments[0] instanceof Regexp)) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdocs
     */
    public function compile(PredicateFactory $f, array &$arguments) {
        assert('$this->arguments_are_valid($arguments)');
        return $f->_property("name")->_matches($arguments[0]);
    }
}
