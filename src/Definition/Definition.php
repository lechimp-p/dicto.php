<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
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
     * TODO: rename to withExplanation
     * @param   string
     * return   self
     */
    public function explain($explanation) {
        $clone = clone $this;
        $clone->explanation = $explanation;
        return $clone;
    }

    /**
     * @return  string
     */
    public function explanation() {
        return $this->explanation;
    }
}

