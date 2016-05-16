<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Variables;

class AsWellAs extends Compound {
    /**
     * @inheritdoc
     */
    public function explain($text) {
        $v = new AsWellAs($this->name(), $this->left, $this->right);
        $v->setExplanation($text);
        return $v;
    }
}
