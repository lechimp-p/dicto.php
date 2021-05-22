<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

class A3
{
    public function use_global_by_keyword()
    {
        global $glob;
    }

    public function use_global_by_array()
    {
        $glob = $GLOBALS["glob"];
    }
}
