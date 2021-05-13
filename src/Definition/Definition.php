<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition;

/**
 * Base class for all definitions. 
 */
abstract class Definition {
    /**
     * @var string|null
     */
    private $explanation = null;

    /**
     * @param   string
     * return   self
     */
    public function withExplanation($explanation) {
        $clone = clone $this;
        $clone->explanation = $explanation;
        return $clone;
    }

    /**
     * @return  string|null
     */
    public function explanation() {
        return $this->explanation;
    }
}

