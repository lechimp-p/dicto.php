<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Analysis;

class Consts {
    const CLASS_ENTITY = "class";
    const FILE_ENTITY = "file";
    const GLOBAL_ENTITY = "global";
    const FUNCTION_ENTITY = "function";
    const BUILDIN_ENTITY = "buildin";

    static $ENTITY_TYPES = array("class","file","global","function", "buildin");
}
