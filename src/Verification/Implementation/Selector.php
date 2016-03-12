<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Verification\Implementation;

use \Lechimp\Dicto\Verification as Verification;
use \Lechimp\Dicto\Definition as Definition;

class Selector implements Verification\Selector {
    /**
     * @inheritdocs
     */
    public function matches(Definition\_Variable $def, Verification\Artifact $artifact) {
    }
}
