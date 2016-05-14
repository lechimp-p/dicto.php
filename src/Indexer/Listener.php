<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Indexer;

use PhpParser\Node as N;

/**
 * Reacts on entering and leaving specific nodes.
 *
 * TODO: At some point it might be worse it to make this an interface.
 * TODO: At some point it might be a good idea to decouple this from
 *       PhpParser. 
 */
class Listener {
    /**
     * @var I\Insert
     */
    protected $insert;

    /**
     * @var string|null
     */
    protected $file_path = null;

    /**
     * @var string[]|null
     */
    protected $file_content = null;

    public function __construct(Insert $insert) {
        $this->insert = $insert;
    }


    public function on_enter_file($id, $path, $content) {
        assert('is_int($id)');
        assert('is_string($path)');
        assert('is_string($content)');
        $this->file_path = $path;
        $this->file_content = explode("\n", $content);
    }

    public function on_leave_file($id) {
        assert('is_int($id)');
        $this->file_path = null;
        $this->file_content = null;
    }

    public function on_enter_class($id, N\Stmt\Class_ $class) {
        assert('is_int($id)');
    }

    public function on_leave_class($id) {
        assert('is_int($id)');
    }

    public function on_enter_method($id, N\Stmt\ClassMethod $method) {
        assert('is_int($id)');
    }

    public function on_leave_method($id) {
        assert('is_int($id)');
    }

    public function on_enter_function($id, N\Stmt\Function_ $function) {
        assert('is_int($id)');
    }

    public function on_leave_function($id) {
        assert('is_int($id)');
    }

    public function on_enter_misc(Insert $insert, Location $location, \PhpParser\Node $node) {
    }

    public function on_leave_misc(Insert $insert, Location $location, \PhpParser\Node $node) {
    }

    // helpers

    protected function lines_from_to($start, $end) {
        assert('is_int($start)');
        assert('is_int($end)');
        return implode("\n", array_slice($this->file_content, $start-1, $end-$start+1));
    }
}
