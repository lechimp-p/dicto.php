<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

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
    public function compile($negate = false, $foo) {
        throw new \LogicException("NYI!");
    }
}
