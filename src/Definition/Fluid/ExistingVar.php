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
 * Provides fluid interface to existing $varname().
 */
class ExistingVar extends Base {
    /**
     * Say to mean this variable and another variable.
     *
     * @return  AsWellAs
     */
    public function as_well_as() {
        return new AsWellAs($this->rt);
    }

    /**
     * Say to mean this variable but not another variable.
     *
     * @return  ButNot
     */
    public function but_not() {
        return new ButNot($this->rt);
    }
}
