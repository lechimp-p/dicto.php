<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Analysis;

use Lechimp\Dicto\Rules\Rule;

class Violation {
    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var int
     */
    protected $line_no;

    /**
     * @var string
     */
    protected $line;

    public function __construct(Rule $rule, $filename, $line_no, $line) {
        $this->rule = $rule;
        assert('is_string($filename)');
        $this->filename = $filename;
        assert('is_int($line_no)');
        $this->line_no = $line_no;
        assert('is_string($line)');
        $this->line = $line;
    }

    /**
     * @return Def\Rules\Rule
     */
    public function rule() {
        return $this->rule;
    }

    /**
     * @return string
     */
    public function filename() {
        return $this->filename;
    }

    /**
     * @return int
     */
    public function line_no() {
        return $this->line_no;
    }

    /**
     * @return string
     */
    public function line() {
        return $this->line;
    }
}
