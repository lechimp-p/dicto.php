<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition\Variables;

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
     * @inheritdoc
     */
    public function explain($text) {
        $v = new Buildins();
        $v->setExplanation($text);
        return $v;
    }
}

