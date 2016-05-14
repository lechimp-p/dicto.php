<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Rules;

use Lechimp\Dicto\Analysis\Consts;
use Lechimp\Dicto\Indexer\Location;
use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Indexer\ListenerRegistry;
use PhpParser\Node as N;

/**
 * A class or function is considered do depend on something if its body
 * of definition makes use of the thing. Language constructs, files or globals
 * can't depend on anything.
 */
class DependOn extends Relation {
    /**
     * @inheritdoc
     */
    public function name() {
        return "depend_on";    
    }

    /**
     * @inheritdoc
     */
    public function register_listeners(ListenerRegistry $registry) {
        $registry->on_enter_misc(function(Insert $insert, Location $location, \PhpParser\Node $node) {
            $ref_ids = array();
            if ($node instanceof N\Expr\MethodCall) {
                // The 'name' could also be a variable like in $this->$method();
                if (is_string($node->name)) {
                    $ref_ids[] = $insert->get_reference
                        ( Consts::METHOD_ENTITY
                        , $node->name
                        , $location->file_path()
                        , $node->getAttribute("startLine")
                        );
                }
            }
            elseif($node instanceof N\Expr\FuncCall) {
                // Omit calls to closures, we would not be able to
                // analyze them anyway atm.
                // Omit functions in arrays, we would not be able to
                // analyze them anyway atm.
                if (!($node->name instanceof N\Expr\Variable ||
                      $node->name instanceof N\Expr\ArrayDimFetch)) {
                    $ref_ids[] = $insert->get_reference
                        ( Consts::FUNCTION_ENTITY
                        , $node->name->parts[0]
                        , $location->file_path()
                        , $node->getAttribute("startLine")
                        );
                }
            }
            elseif ($node instanceof N\Stmt\Global_) {
                foreach ($node->vars as $var) {
                    if (!($var instanceof N\Expr\Variable) || !is_string("$var->name")) {
                        throw new \RuntimeException(
                            "Expected Variable with string name, found: ".print_r($var, true));
                    }
                    $ref_ids[] = $insert->get_reference
                        ( Consts::GLOBAL_ENTITY
                        , $var->name
                        , $location->file_path()
                        , $node->getAttribute("startLine")
                        );
                }
            }
            elseif ($node instanceof N\Expr\ArrayDimFetch) {
                if ($node->var instanceof N\Expr\Variable && $node->var->name == "GLOBALS") {
                    // Ignore usage of $GLOBALS with variable index.
                    if (!($node->dim instanceof N\Expr\Variable)) {
                        $ref_ids[] = $insert->get_reference
                            ( Consts::GLOBAL_ENTITY
                            , $node->dim->value
                            , $location->file_path()
                            , $node->getAttribute("startLine")
                            );
                    }
                }
            }
            elseif ($node instanceof N\Expr\ErrorSuppress) {
                $ref_ids[] = $insert->get_reference
                        ( Consts::LANGUAGE_CONSTRUCT_ENTITY
                        , "@"
                        , $location->file_path()
                        , $node->getAttribute("startLine")
                        );
            }
            foreach ($ref_ids as $ref_id) {
                $start_line = $node->getAttribute("startLine");
                $source_line = $location->file_content($start_line, $start_line);
                // Record a dependency for every entity we currently know as dependent.
                foreach ($location->in_entities() as $entity) {
                    if ($entity[0] == Consts::FILE_ENTITY) {
                        continue;
                    }
                    $insert->relation
                        ( "depend_on"
                        , $entity[1]
                        , $ref_id
                        , $location->file_path()
                        , $start_line
                        , $source_line
                        );
                }
            }
        });
    }
}