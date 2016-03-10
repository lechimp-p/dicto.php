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
class _With {
    /**
     * @var Variable
     */
    private $other;

    public function __construct(Variable $other) {
        $this->other = $other;
    }

    public function _name($regexp) {
        return new _WithName($regexp, $this->other); 
    }
}
