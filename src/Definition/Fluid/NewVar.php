<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition\Fluid;
use Lechimp\Dicto\Definition as Def;

/**
 * A new variable definition was started.
 */
class NewVar extends Base {
    /**
     * Define what the variable means.
     *
     * @return  Means
     */
    public function means() {
        return new Means($this->rt);
    }
}
