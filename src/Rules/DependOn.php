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

use Lechimp\Dicto\Variables\Variable;
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
        $registry->on_enter_misc
            ( array(N\Expr\MethodCall::class, N\Expr\FuncCall::class,
                    N\Stmt\Global_::class, N\Expr\ArrayDimFetch::class,
                    N\Expr\ErrorSuppress::class)
            , function(Insert $insert, Location $location, \PhpParser\Node $node) {
                $ref_ids = array();
                if ($node instanceof N\Expr\MethodCall) {
                    // The 'name' could also be a variable like in $this->$method();
                    if (is_string($node->name)) {
                        $ref_ids[] = $insert->get_reference
                            ( Variable::METHOD_TYPE
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
                            ( Variable::FUNCTION_TYPE
                            , $node->name->parts[0]
                            , $location->file_path()
                            , $node->getAttribute("startLine")
                            );
                    }
                }
                elseif ($node instanceof N\Stmt\Global_) {
                    foreach ($node->vars as $var) {
                        if (!($var instanceof N\Expr\Variable) || !is_string($var->name)) {
                            throw new \RuntimeException(
                                "Expected Variable with string name, found: ".print_r($var, true));
                        }
                        $ref_ids[] = $insert->get_reference
                            ( Variable::GLOBAL_TYPE
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
                                ( Variable::GLOBAL_TYPE
                                , $node->dim->value
                                , $location->file_path()
                                , $node->getAttribute("startLine")
                                );
                        }
                    }
                }
                elseif ($node instanceof N\Expr\ErrorSuppress) {
                    $ref_ids[] = $insert->get_reference
                            ( Variable::LANGUAGE_CONSTRUCT_TYPE
                            , "@"
                            , $location->file_path()
                            , $node->getAttribute("startLine")
                            );
                }
                foreach ($ref_ids as $ref_id) {
                    $this->insert_relation_into
                        ( $insert
                        , $location
                        , $node->getAttribute("startLine")
                        , $ref_id
                        );
                }
            });
        }
}
