<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Analysis\Query;
use Lechimp\Dicto\Indexer\Insert;

class NullDB implements Insert, Query {
    // Insert
    public function _file($path, $source) { return 0; }
    public function _class($name, $file, $start_line, $end_line) { return 0; }
    public function _method($name, $class, $file, $start_line, $end_line) { return 0; }
    public function _function($name, $file, $start_line, $end_line) { return 0; }
    public function _global($name) { return 0; }
    public function _language_construct($name, $file, $line) { return 0; }
    public function _method_reference($name, $file, $line) { return 0; }
    public function _function_reference() { return 0; }
    public function _relation($left_entity, $right_entity, $file, $line) { return 0; }
    // Query
    public function name_table() { return "names"; }
    public function file_table() { return "files"; }
    public function source_table() { return "source"; }
    public function definition_table() { return "definitions"; }
    public function method_info_table() { return "method_info"; }
    public function relation_table() { return "relations"; }
    public function builder() { throw new \RuntimeException("PANIC!"); }
}
