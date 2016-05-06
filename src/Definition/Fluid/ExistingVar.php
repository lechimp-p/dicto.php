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

/**
 * Provides fluid interface to entities that were already defined before, at
 * least a bit.
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

    /**
     * Say that you want to state some properties of the variable.
     *
     * @return  With
     */
    public function with() {
        return new With($this->rt);
    }

    /**
     * Explain something about the variable.
     *
     * @return  null
     */
    public function explain($explanation) {
    }
}
