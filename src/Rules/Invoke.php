<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Rules;

/**
 * A class of function is considered to invoke something, it that thing is invoked
 * in its body.
 */
class Invoke extends Relation {
    /**
     * @inheritdoc
     */
    public function name() {
        return "invoke";    
    }
}
