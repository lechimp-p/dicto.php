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
 * Basic interface to an algorithm that checks violations of rules on
 * artifacts.
 */
interface Verifier {
    /**
     * Check whether an artifact is subject of a rule.
     *
     * @param   Definition\Rule     $rule
     * @param   Artifact            $artifact
     * @return  bool
     */
    public function has_subject(Definition\Rule $rule, Artifact $artifact);

    /**
     * Get violations of a rule on an artifact.
     *
     * @param   Definition\Rule     $rule
     * @param   Artifact            $artifact
     * @return  Violation[]
     */
    public function violations_in(Definition\Rule $rule, Artifact $artifact);
}
