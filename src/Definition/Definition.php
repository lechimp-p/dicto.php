<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
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
     */
    abstract public function explain($explanation);

    protected function setExplanation($explanation) {
        assert('is_string($explanation)');
        $this->explanation = $explanation;
    }
}

