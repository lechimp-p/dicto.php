<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\App;

/**
 * Get the current state of the sourcecode.
 */
interface SourceStatus {
    /**
     * Get the commit hash of the source.
     *
     * @return  string
     */
    public function commit_hash();
}
