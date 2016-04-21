<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Indexer\PhpParser;

use Lechimp\Dicto\Analysis\Consts;
use Lechimp\Dicto\Indexer as I;
use PhpParser\Node as N;

/**
 * Detects dependencies.
 */
class DependenciesListener extends Listener {
    /**
     * @var int[]|null
     */
    protected $dependent_entity_ids = array();


    public function on_enter_class($id, N\Stmt\Class_ $class) {
        assert('is_int($id)');
        // Every interesting reference we find will create a dependency
        // on this class.
        $this->dependent_entity_ids[] = $id;
    }

    public function on_leave_class($id) {
        assert('is_int($id)');
        // Done with the dependencies of this class.
        $this->remove_from($id, $this->dependent_entity_ids);
    }

    public function on_enter_method($id, N\Stmt\ClassMethod $method) {
        assert('is_int($id)');
        // Every interesting reference we find will create a dependency
        // on this method.
        $this->dependent_entity_ids[] = $id;
    }

    public function on_leave_method($id) {
        assert('is_int($id)');
        // Done with the dependencies of this method.
        $this->remove_from($id, $this->dependent_entity_ids);
    }

    public function on_enter_function($id, N\Stmt\Function_ $function) {
        assert('is_int($id)');
        // Every interesting reference we find will create a dependency
        // on this function.
        $this->dependent_entity_ids[] = $id;
    }

    public function on_leave_function($id) {
        assert('is_int($id)');
        // Done with the dependencies of this function.
        $this->remove_from($id, $this->dependent_entity_ids);
    }

    public function on_enter_misc(\PhpParser\Node $node) {
        $ref_id = null;
        if ($node instanceof N\Expr\MethodCall) {
            $ref_id = $this->indexer->get_reference(Consts::METHOD_ENTITY, $node->name);
        }
        elseif($node instanceof N\Expr\FuncCall) {
            $ref_id = $this->indexer->get_reference(Consts::FUNCTION_ENTITY, $node->name);
        }
        if ($ref_id !== null) {
            $start_line = $node->getAttribute("startLine");
            $source_line = $this->lines_from_to($start_line, $start_line);
            // We are in a class a need to record the dependency now.
            foreach ($this->dependent_entity_ids as $dependent_id) {
                $this->insert->dependency
                    ( $dependent_id
                    , $ref_id
                    , $this->file_path
                    , $start_line
                    , $source_line
                    );
            }
        }
    }

    public function on_leave_misc(\PhpParser\Node $node) {
    }
}
