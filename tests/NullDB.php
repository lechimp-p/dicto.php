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
    public function source_file($name, $content){return 0;}
    public function entity($type, $name, $file, $start_line, $end_line){return 0;}
    public function reference($type, $name, $file, $line){return 0;}
    public function get_reference($type, $name, $file, $line){return 0;}
    public function relation($name, $entity_id, $reference_id, $file, $line){return 0;}
    public function name_table() { return "names"; }
    public function file_table() { return "files"; }
    public function source_table() { return "source"; }
    public function definition_table() { return "definitions"; }
    public function reference_table() { return "refs"; }
    public function relation_table() { return "relations"; }
    public function builder() { throw new \RuntimeException("PANIC!"); }
}
