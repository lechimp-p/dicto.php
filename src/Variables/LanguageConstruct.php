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

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

class LanguageConstruct extends Variable {
    /**
     * @var string
     */
    private $construct_name;

    public function __construct($name, $construct_name) {
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
     * @inheritdocs
     */
    public function compile(ExpressionBuilder $builder, $table_name, $negate = false) {
    }
}

