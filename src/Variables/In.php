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

use Lechimp\Dicto\Definition\ArgumentParser;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

/**
 * Name is a property, right?
 */
class In extends Property {
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
    public function compile(Variable $variable, array &$arguments, ExpressionBuilder $builder, $table_name, $negate = false) {
        assert('$this->arguments_are_valid($arguments)');
        if (!($variable instanceof Methods)) {
            throw new \LogicException("Property 'in' only works with methods, but not with '".get_class($variable)."'.");
        }

        if (!$negate) {
            return "1 = 1";
        }
        else {
            return "1 = 1";
        }
    }
}
