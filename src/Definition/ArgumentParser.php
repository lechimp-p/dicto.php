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

use Lechimp\Dicto\Variables\Variable;

/**
 * Fetches arguments for parsing the rules.
 */
interface ArgumentParser {
    /**
     * Fetch a string from the token stream.
     *
     * If this is the not the first argument that was fetched, this must also
     * fetch a trailing "," without outputting it, to separate arguments.
     *
     * @return  string
     */
    public function fetch_string();

    /**
     * Fetch a variable from the token stream.
     *
     * If this is the not the first argument that was fetched, this must also
     * fetch a trailing "," without outputting it, to separate arguments.
     *
     * @return  Variable
     */
    public function fetch_variable();
}
