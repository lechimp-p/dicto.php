<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition\AST;

/**
 * An atom is something like a keyword and not a name.
 *
 * Atoms look like ^[a-z]+(\s+[a-z]+)?$
 */
class Atom extends Definition {
    /**
     * @var string
     */
    protected $atom;

    public function __construct($atom) {
        assert('is_string($atom)');
        $this->check($atom);
        $this->atom = $atom;
    }

    /**
     * @return  string
     */
    public function __toString() {
        return $this->atom;
    }

    /**
     * @param   string
     * @throws  \InvalidArgumentException if string is not a valid atom.
     * @return  null
     */
    protected function check($atom) {
        if (preg_match("%^[a-z]+(\s+[a-z]+)?$%", $atom) !== 1) {
            throw new \InvalidArgumentException("Invalid atom '$atom'");
        }
    }
}
