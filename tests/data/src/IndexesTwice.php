<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

class IndexesTwice
{
    public function indexes_twice()
    {
        return $foo["bar"]["batz"];
    }

    public function indexes_GLOBAL_twice()
    {
        return $GLOBALS["glob"]["bar"];
    }
}
