<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

use Lechimp\Dicto\Indexer\Insert;

/**
 * A database for the indexer based on graph.
 */
class IndexDB extends Graph implements Insert {
    /**
     * @inheritdocs
     */
    public function name($name, $type) {}

    /**
     * @inheritdocs
     */
    public function file($path) {
        return $this->create_node("file", ["path" => $path])->id();
    }

    /**
     * @inheritdocs
     */
    public function source($path, $content) {
        assert('is_string($content)');
        $file_id = $this->file($path);
        $file = $this->node($file_id);
        $num = 1;
        foreach (explode("\n", $content) as $source) {
            $line = $this->create_node("line", ["num" => $num, "source" => $source]);
            $this->add_relation($file, "contains", [], $line);
            $num++;
        }
    }

    /**
     * @inheritdocs
     */
    public function definition($name, $type, $file, $start_line, $end_line) {}

    /**
     * @inheritdocs
     */
    public function method_info($name_id, $class_name_id, $definition_id) {}

    /**
     * @inheritdocs
     */
     public function relation($name_left_id, $name_right_id, $which, $file, $line) {}
}
