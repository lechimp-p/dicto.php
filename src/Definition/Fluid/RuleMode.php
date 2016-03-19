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
 * Provides fluid interface to only()..can(), must() and cannot().
 */
class RuleMode extends BaseWithNameAndMode  {
    public function contain_text($regexp) {
        $var = $this->rt->get_var($this->name);
        $this->rt->add_rule(
            new Def\Rules\ContainText($this->mode, $var, $regexp));
   }

    public function invoke() {
        return new Invoke($this->rt, $this->name, $this->mode);
    }

    public function depend_on() {
        return new DependOn($this->rt, $this->name, $this->mode);
    }

}
