<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Rules;

use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\Indexer\ASTVisitor;
use Lechimp\Dicto\Indexer\Location;
use Lechimp\Dicto\Indexer\Insert;
use PhpParser\Node as N;

/**
 * A class of function is considered to invoke something, it that thing is invoked
 * in its body.
 */
class Invoke extends Relation implements ASTVisitor {
    /**
     * @inheritdoc
     */
    public function name() {
        return "invoke";
    }

    /**
     * @inheritdoc
     */
    public function visitorJumpLabels() {
        return
            [ N\Expr\MethodCall::class => "enterMethodCall"
            , N\Expr\FuncCall::class => "enterFunctionCall"
            , N\Expr\Exit_::class => "enterExit"
            , N\Expr\Eval_::class => "enterEval"
            ];
    }

    public function enterMethodCall(Insert $insert, Location $location, N\Expr\MethodCall $node) {
        // The 'name' could also be a variable like in $this->$method();
        if (is_string($node->name)) {
            $method_reference = $insert->_method_reference
                ( $node->name
                , $location->_file()
                , $location->_line()
                , $location->_column()
                );
            $this->insert_relation_into
                ( $insert
                , $location
                , $method_reference
                );
        }
    }

    public function enterFunctionCall(Insert $insert, Location $location, N\Expr\FuncCall $node) {
        // Omit calls to closures, we would not be able to
        // analyze them anyway atm.
        // Omit functions in arrays, we would not be able to
        // analyze them anyway atm.
        if (!($node->name instanceof N\Expr\Variable ||
              $node->name instanceof N\Expr\ArrayDimFetch)) {
            $function_reference = $insert->_function_reference
                ( $node->name->parts[0]
                , $location->_file()
                , $location->_line()
                , $location->_column()
                );
            $this->insert_relation_into
                ( $insert
                , $location
                , $function_reference
                );
        }
    }

    public function enterExit(Insert $insert, Location $location, N\Expr\Exit_ $node) {
        if ($node->getAttribute("kind") == N\Expr\Exit_::KIND_EXIT) {
            $kind = "exit";
        }
        else {
            $kind = "die";
        }
        $exit_or_die = $insert->_language_construct
            ( $kind
            );
        $this->insert_relation_into
            ( $insert
            , $location
            , $exit_or_die
            );
    }

    public function enterEval(Insert $insert, Location $location, N\Expr\Eval_ $node) {
        $eval_ = $insert->_language_construct("eval");
        $this->insert_relation_into
            ( $insert
            , $location
            , $eval_
            );
    }
}
