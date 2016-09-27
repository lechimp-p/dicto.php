<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
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
        return "depend on";
    }

    /**
     * @inheritdoc
     */
    public function register_listeners(ListenerRegistry $registry) {
        $this->register_method_call_listener($registry);
        $this->register_func_call_listener($registry);
        $this->register_global_listener($registry);
        $this->register_array_dim_fetch_listener($registry);
        $this->register_error_suppressor_listener($registry);
    }

    protected function register_method_call_listener(ListenerRegistry $registry) {
        $registry->on_enter_misc
            ( array(N\Expr\MethodCall::class)
            , function(Insert $insert, Location $location, N\Expr\MethodCall $node) {
                // The 'name' could also be a variable like in $this->$method();
                if (is_string($node->name)) {
                    $method_reference = $insert->_method_reference
                        ( $node->name
                        , $location->in_entities()[0][1]
                        , $node->getAttribute("startLine")
                        );
                    $this->insert_relation_into
                        ( $insert
                        , $location
                        , $method_reference
                        , $node->getAttribute("startLine")
                        );
                }
            });
    }

    protected function register_func_call_listener(ListenerRegistry $registry) {
        $registry->on_enter_misc
            ( array(N\Expr\FuncCall::class)
            , function(Insert $insert, Location $location, N\Expr\FuncCall $node) {
                // Omit calls to closures, we would not be able to
                // analyze them anyway atm.
                // Omit functions in arrays, we would not be able to
                // analyze them anyway atm.
                if (!($node->name instanceof N\Expr\Variable ||
                      $node->name instanceof N\Expr\ArrayDimFetch)) {
                    $function_reference = $insert->_function_reference
                        ( $node->name->parts[0]
                        , $location->in_entities()[0][1]
                        , $node->getAttribute("startLine")
                        );
                    $this->insert_relation_into
                        ( $insert
                        , $location
                        , $function_reference
                        , $node->getAttribute("startLine")
                        );
                }
            });
    }

    protected function register_global_listener(ListenerRegistry $registry) {
        $registry->on_enter_misc
            ( array(N\Stmt\Global_::class)
            , function(Insert $insert, Location $location, N\Stmt\Global_ $node) {
                foreach ($node->vars as $var) {
                    if (!($var instanceof N\Expr\Variable) || !is_string($var->name)) {
                        throw new \RuntimeException(
                            "Expected Variable with string name, found: ".print_r($var, true));
                    }
                    $global = $insert->_global($var->name);
                    $this->insert_relation_into
                        ( $insert
                        , $location
                        , $global
                        , $node->getAttribute("startLine")
                        );
                }
            });
    }

    protected function register_array_dim_fetch_listener(ListenerRegistry $registry) {
        $registry->on_enter_misc
            ( array(N\Expr\ArrayDimFetch::class)
            , function(Insert $insert, Location $location, N\Expr\ArrayDimFetch $node) {
                if ($node->var instanceof N\Expr\Variable 
                &&  $node->var->name == "GLOBALS"
                // Ignore usage of $GLOBALS with variable index.
                && !($node->dim instanceof N\Expr\Variable)) {
                    $global = $insert->_global($node->dim->value);
                    $this->insert_relation_into
                        ( $insert
                        , $location
                        , $global
                        , $node->getAttribute("startLine")
                        );
                }
            });
    }

    protected function register_error_suppressor_listener(ListenerRegistry $registry) {
        $registry->on_enter_misc
            ( array(N\Expr\ErrorSuppress::class)
            , function(Insert $insert, Location $location, N\Expr\ErrorSuppress $node) {
                $language_construct = $insert->_language_construct("@");
                $this->insert_relation_into
                    ( $insert
                    , $location
                    , $language_construct
                    , $node->getAttribute("startLine")
                    );
            });
        }
}
