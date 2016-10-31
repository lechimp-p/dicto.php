<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto;

/**
 * Small wrapper around preg.
 */
class Regexp {
    /**
     * @var string
     */
    protected $regexp;

    /**
     * @var string
     */
    protected $delim = "%";

    public function __construct($regexp) {
        assert('is_string($regexp)');
        if (@preg_match($this->delim.$regexp.$this->delim, "") === false) {
            throw new \InvalidArgumentException("Invalid regexp '$regexp'");
        }
        $this->regexp = $regexp;
    }

    /**
     * @return  string
     */
    public function raw() {
        return $this->regexp;
    }

    /**
     * Match a string with the regexp.
     *
     * @param   string      $str
     * @param   bool        $dotall
     * @param   array|null  $matches
     * @return  bool
     */
    public function match($str, $dotall = false, &$matches = null) {
        if (!$dotall) {
            return preg_match($this->delim."^".$this->regexp.'$'.$this->delim, $str, $matches) === 1;
        }
        else {
            return preg_match($this->delim."^".$this->regexp.'$'.$this->delim."s", $str, $matches) === 1;
        }
    }

    /**
     * Match the beginning of a string with the regexp.
     *
     * @param   string      $str
     * @param   bool        $dotall
     * @param   array|null  $matches
     * @return  bool
     */
    public function match_beginning($str, $dotall = false, &$matches = null) {
        if (!$dotall) {
            return preg_match($this->delim."^".$this->regexp.$this->delim, $str, $matches) === 1;
        }
        else {
            return preg_match($this->delim."^".$this->regexp.$this->delim."s", $str, $matches) === 1;
        }
    }

    /**
     * Search a string with the regexp.
     *
     * @param   string      $str
     * @param   bool        $dotall
     * @param   array|null  $matches
     * @return  bool
     */
    public function search($str, $dotall = false, &$matches = null) {
        if (!$dotall) {
            return preg_match($this->delim.$this->regexp.$this->delim, $str, $matches) === 1;
        }
        else {
            return preg_match($this->delim.$this->regexp.$this->delim."s", $str, $matches) === 1;
        }
    }
}

