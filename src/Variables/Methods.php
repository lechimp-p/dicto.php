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

class Methods extends Entities {
    /**
     * @inheritdoc
     */
    public function id() {
        return Variable::METHOD_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function meaning() {
        return "methods";
    }
}

