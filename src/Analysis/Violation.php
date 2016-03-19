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
use Lechimp\Dicto\Definition as Def;

class Violation {
    /**
     * @var Def\Rules\Rule
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

    /**
     * @var string[]
     */
    protected $lines_before;

    /**
     * @var string[]
     */
    protected $lines_after;

    public function __construct(Def\Rules\Rule $rule, $filename, $line_no, 
                                $line, array $lines_before, array $lines_after) {
        $this->rule = $rule;
        assert('is_string($filename)');
        $this->filename = $filename;
        assert('is_int($line_no)');
        $this->line_no = $line_no;
        assert('is_string($line)');
        $this->line = $line;
        assert('$this->is_string_list($lines_before)');
        $this->lines_before = $lines_before;
        assert('$this->is_string_list($lines_after)');
        $this->lines_after = $lines_after;
    }

    protected function is_string_list(array $as) {
        foreach ($as as $a) {
            if (!is_string($a)) {
                return false;
            }
        }
        return true;
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

    /**
     * @return string[]
     */
    public function lines_before() {
        return $this->lines_before;
    }

    /**
     * @return string[]
     */
    public function lines_after() {
        return $this->lines_after;
    }
}
