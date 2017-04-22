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
 * An explanation explains the next line.
 */
class Explanation extends Line {
    /**
     * @var string
     */
    protected $content;

    public function __construct($content) {
        assert('is_string($content)');
        $this->content = $content;
    }

    /**
     * @return  string
     */
    public function content() {
        return $this->content;
    }
}

