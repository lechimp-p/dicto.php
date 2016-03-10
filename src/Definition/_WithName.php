<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition;

/**
 * Provides fluid interface to _with.
 */
class _WithName {
    /**
     * @var string
     */
    private $regexp;

    /**
     * @var Variable
     */
    private $other;

    public function __construct($regexp, Variable $other) {
        preg_match("%$regexp%", "");
        $this->regexp = $regexp;
        $this->other = $other;
    }
}
