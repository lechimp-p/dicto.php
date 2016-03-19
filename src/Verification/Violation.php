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

use \Lechimp\Dicto\Definition as Def;

class Violation {
    /**
     * @var Artifact
     */
    private $artifact;

    /**
     * @var Def\Rules\Rule
     */
    private $rule;

    /**
     * @var Artifact
     */
    private $violator;

    public function __construct(Artifact $artifact, Def\Rules\Rule $rule, Artifact $violator) {
        $this->artifact = $artifact;
        $this->rule = $rule;
        $this->violator = $violator;
    }

    /**
     * @return Artifact
     */
    public function artifact() {
        return $this->artifact;
    }

    /**
     * @return Definition\Rule
     */
    public function rule() {
        return $this->rule;
    }

    /**
     * @return Artifact
     */
    public function violator() {
        return $this->violator;
    }
}
