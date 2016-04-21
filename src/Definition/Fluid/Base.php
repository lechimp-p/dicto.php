<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Definition\Fluid;
use Lechimp\Dicto\Definition as Def;

/**
 * Base class for all fluid interfaces.
 */
class Base {
    /**
     * @var Def\RuleDefinitionRT
     */ 
    protected $rt;

    public function __construct(Def\RuleDefinitionRT $rt) {
        $this->rt = $rt;
    }
}
