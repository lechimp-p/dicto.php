<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Graph\PredicateFactory;

class Everything extends Variable {
    public function __construct() {
        parent::__construct("Everything");
    }

    /**
     * @inheritdocs
     */
    public function meaning() {
        return "everything";
    }

    /**
     * @inheritdocs
     */

    public function compile(PredicateFactory $f) {
        return $f->_true();
    }
}

