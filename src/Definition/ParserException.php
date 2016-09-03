<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition;

/**
 * Exceptions created during parsing.
 */
class ParserException extends \Exception {
    /**
     * @var int|null
     */
    protected $s_line = null;

    /**
     * @var int|null
     */
    protected $s_column = null;

    /**
     * @return  int|null
     */
    public function line() {
        return $this->s_line;
    }

    /**
     * @return  int|null
     */
    public function column() {
        return $this->s_column;
    }

    /**
     * @param   int     $line
     * @param   int     $column
     * @return  null
     */
    public function setPosition($line, $column) {
        assert('is_null($this->s_line)');
        assert('is_null($this->s_column)');
        assert('is_int($line)');
        assert('is_int($column)');
        $this->s_line = $line;
        $this->s_column = $column;
        $this->message = "At line $line, column $column: $this->message";
    }
}

