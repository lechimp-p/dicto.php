<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Indexer;

use PhpParser\Node as N;

/**
 * Reacts on entering and leaving specific nodes.
 *
 * TODO: At some point it might be worse it to make this an interface.
 * TODO: At some point it might be a good idea to decouple this from
 *       PhpParser. 
 */
class Listener {
    public function on_enter_misc(Insert $insert, Location $location, \PhpParser\Node $node) {
    }

    public function on_leave_misc(Insert $insert, Location $location, \PhpParser\Node $node) {
    }
}
