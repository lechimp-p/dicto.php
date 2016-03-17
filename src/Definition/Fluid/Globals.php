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

class Globals extends Base {
    public function __construct(Def\RuleDefinitionRT $rt) {
        parent::__construct($rt);

        // This is the first valid definition of a variable.
        $this->rt->current_var_is(new Vars\Globals($this->rt->get_current_var_name()));
    }

    public function with() {
        return new With;
    }

    public function explain($explanation) {
    }
}
