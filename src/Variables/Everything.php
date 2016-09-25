<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Graph\Node;

class Everything extends Variable {
    /**
     * @inheritdocs
     */
    public function meaning() {
        return "everything";
    }

    /**
     * @inheritdocs
     */

    public function compile($negate = false) {
        return function(Node $n) use ($negate) { return !$negate; };
    }
}

