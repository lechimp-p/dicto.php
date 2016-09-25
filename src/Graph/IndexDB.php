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
    public function name($name, $type) {
        $res = (new QueryImpl)
            ->with_filter(function(Node $n) use ($name) {
                return $n->type() == "name"
                    && $n->property("name") == $name;
            })
            ->execute_on($this);
        if ($res->is_empty()) {
            return $this->create_node("name", ["name" => $name, "type" => $type])->id();
        }
        $res = $res
            ->extract(function ($n) {
                return $n->id();
            });
        assert('count($res) == 1');
        return $res[0];
    }

    /**
     * @inheritdocs
     */
    public function file($path) {
        $res = (new QueryImpl)
            ->with_filter(function(Node $n) use ($path) {
                return $n->type() == "file"
                    && $n->property("path") == $path;
            })
            ->execute_on($this);

        if ($res->is_empty()) {
            return $this->create_node("file", ["path" => $path])->id();
        }
        $res = $res
            ->extract(function ($n) {
                return $n->id();
            });
        assert('count($res) == 1');
        return $res[0];
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
        return $file_id;
    }

    /**
     * @inheritdocs
     */
    public function definition($name, $type, $file, $start_line, $end_line) {
        $name_id = $this->name($name, $type);
        $name = $this->node($name_id);
        $file_id = $this->file($file);
        $file = $this->node($file_id);
        $definition = $this->create_node("definition",
            ["start_line" => $start_line, "end_line" => $end_line]);
        $this->add_relation($name, "has_definition", [], $definition);
        $this->add_relation($definition, "in_file", [], $file);
        return array($name_id, $definition->id());
    }

    /**
     * @inheritdocs
     */
    public function method_info($name_id, $class_name_id, $definition_id) {
        $name = $this->node($name_id);
        $class_name = $this->node($class_name_id);
        $this->add_relation($name, "in_class", ["def_id" => $definition_id], $class_name);
    }

    /**
     * @inheritdocs
     */
     public function relation($name_left_id, $name_right_id, $which, $file, $line) {
        $name_left = $this->node($name_left_id);
        $name_right = $this->node($name_right_id);
        $this->add_relation($name_left, $which, ["path" => $file, "line" => $line], $name_right);
     }
}
