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

use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Analysis\Query;

/**
 * A class or function is considered do depend on something if its body
 * of definition makes use of the thing. Language constructs, files or globals
 * can't depend on anything.
 */
class DependOn extends Relation {
    /**
     * @inheritdoc
     */
    public function name() {
        return "depend_on";    
    }
}
