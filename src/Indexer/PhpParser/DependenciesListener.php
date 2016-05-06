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
use PhpParser\Node as N;

/**
 * Detects dependencies.
 */
class DependenciesListener extends Listener {
    /**
     * @var int[]
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
        $ref_ids = array();
        if ($node instanceof N\Expr\MethodCall) {
            $ref_ids[] = $this->indexer->get_reference
                ( Consts::METHOD_ENTITY
                , $node->name
                , $node->getAttribute("startLine")
                );
        }
        elseif($node instanceof N\Expr\FuncCall) {
            // Omit calls to closures, we would not be able to
            // analyze them anyway atm.
            if (!($node->name instanceof N\Expr\Variable)) {
                $ref_ids[] = $this->indexer->get_reference
                    ( Consts::FUNCTION_ENTITY
                    , $node->name->parts[0]
                    , $node->getAttribute("startLine")
                    );
            }
        }
        elseif ($node instanceof N\Stmt\Global_) {
            foreach ($node->vars as $var) {
                assert('$var instanceof \\PhpParser\\Node\\Expr\\Variable');
                assert('is_string($var->name)');
                $ref_ids[] = $this->indexer->get_reference
                    ( Consts::GLOBAL_ENTITY
                    , $var->name
                    , $node->getAttribute("startLine")
                    );
            }
        }
        elseif ($node instanceof N\Expr\ArrayDimFetch) {
            if ($node->var instanceof N\Expr\Variable && $node->var->name == "GLOBALS") {
                $ref_ids[] = $this->indexer->get_reference
                    ( Consts::GLOBAL_ENTITY
                    , $node->dim->value
                    , $node->getAttribute("startLine")
                    );
            }
        }
        elseif ($node instanceof N\Expr\ErrorSuppress) {
            $ref_ids[] = $this->indexer->get_reference
                    ( Consts::LANGUAGE_CONSTRUCT_ENTITY
                    , "@"
                    , $node->getAttribute("startLine")
                    );
        }
        foreach ($ref_ids as $ref_id) {
            $start_line = $node->getAttribute("startLine");
            $source_line = $this->lines_from_to($start_line, $start_line);
            // Record a dependency for every entity we currently know as dependent.
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
