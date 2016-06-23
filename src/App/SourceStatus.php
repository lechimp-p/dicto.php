<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\App;

/**
 * Describes the current state of the source code under scrutiny.
 */
class SourceStatus {
    /**
     * @var string
     */
    private $commit_hash;

    public function __construct($commit_hash) {
        assert('is_string($commit_hash)');
        $this->commit_hash = $commit_hash;
    }

    /**
     * @return  string
     */
    public function commit_hash() {
        return $this->commit_hash();
    }
}
