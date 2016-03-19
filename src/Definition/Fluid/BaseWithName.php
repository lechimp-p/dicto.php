<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition\Fluid;
use Lechimp\Dicto\Definition as Def;

/**
 * Base class for all fluid interfaces.
 */
class BaseWithName extends Base {
    /**
     * @var string
     */
    protected $name;

    public function __construct(Def\RuleDefinitionRT $rt, $name) {
        parent::__construct($rt);
        assert('is_string($name)');
        $this->name = $name;
    }
}
