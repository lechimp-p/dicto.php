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

class ErrorSuppressor extends LanguageConstruct {
    public function __construct($name = null) {
        if ($name === null) {
            $name = "ErrorSuppressor";
        }
        parent::__construct("@", $name);
    }
}

