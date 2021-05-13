<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Graph\PredicateFactory;

class Except extends Combinator {
    /**
     * @inheritdocs
     */
    public function id() {
        return "except";
    }

    /**
     * @inheritdocs
     */
    public function compile(PredicateFactory $f) {
        $l = $this->left()->compile($f);
        $r = $this->right()->compile($f);
        return $f->_and
            ([$l
            , $f->_not($r)
            ]);
    }
}
