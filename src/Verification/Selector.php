<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Verification;

use \Lechimp\Dicto\Definition as Definition;

/**
 * Interface to an algorithm that decides whether an artifact matches
 * a variable definition.
 */
interface Selector {
    /**
     * Does the variable definition match the artifact?
     *
     * @param   Definition\Variable    $def
     * @param   Artifact                $artifact
     * @return  bool
     */
    public function matches(Definition\Variable $def, Artifact $artifact);
}
