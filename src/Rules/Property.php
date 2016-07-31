<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Rules;

use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Indexer\ListenerRegistry;

/**
 * This is a rule that checks a property on an entity in the code. 
 */
abstract class Property extends Schema {
    /**
     * No listeners per default.
     *
     * @inheritdoc
     */
    public function register_listeners(ListenerRegistry $registry) {
    }
}
