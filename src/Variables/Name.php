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
        $regexp = $parser->fetch_string();
        return array($regexp);
    }

    /**
     * @inheritdocs
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
     * @inheritdocs
     */
    public function compile(Variable $variable, array &$arguments, ExpressionBuilder $builder, $table_name, $negate = false) {
        assert('$this->arguments_are_valid($arguments)');
        if (!$negate) {
            return "$table_name.name REGEXP ".$builder->literal('^'.$arguments[0].'$');
        }
        else {
            return "$table_name.name NOT REGEXP ".$builder->literal('^'.$arguments[0].'$');
        }
    }
}
