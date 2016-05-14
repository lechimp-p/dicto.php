<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Variables;

class Methods extends Variable {
    /**
     * @inheritdoc
     */
    public function explain($text) {
        $v = new Methods($this->name());
        $v->setExplanation($text);
        return $v;
    }
}

