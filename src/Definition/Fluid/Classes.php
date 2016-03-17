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
use Lechimp\Dicto\Definition\Variables as Vars;

/**
 * Provides fluid interface for classes().
 */
class Classes extends Base {
    public function __construct(Def\RuleDefinitionRT $rt) {
        parent::__construct($rt);
    }

    public function with() {
        return new With;
    }

    public function explain($explanation) {
    }
}
