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

class RuleVar extends BaseWithName {
    public function cannot() {
        return new RuleMode($this->rt, $this->name, Def\Rule::MODE_CANNOT);
    }

    public function must() {
        return new RuleMode($this->rt, $this->name, Def\Rule::MODE_MUST);
    }

    public function can() {
        return new RuleMode($this->rt, $this->name, Def\Rule::MODE_ONLY_CAN);
    }
}