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
 * A class or function is considered do depend on something if its body
 * of definition makes use of the thing. Language constructs, files or globals
 * can't depend on anything.
 */
class DependOn extends Relation implements ASTVisitor
{
    /**
     * @inheritdoc
     */
    public function name()
    {
        return "depend on";
    }

    /**
     * @inheritdoc
     */
    public function visitorJumpLabels()
    {
        return
            [ N\Expr\MethodCall::class => "enterMethodCall"
            , N\Expr\FuncCall::class => "enterFunctionCall"
            , N\Stmt\Global_::class => "enterGlobal"
            , N\Expr\ArrayDimFetch::class => "enterArrayDimFetch"
            , N\Expr\ErrorSuppress::class => "enterErrorSuppress"
            ];
    }

    public function enterMethodCall(Insert $insert, Location $location, N\Expr\MethodCall $node)
    {
        // The 'name' could also be a variable like in $this->$method();
        if ($node->name instanceof N\Identifier) {
            $method_reference = $insert->_method_reference(
                    $node->name,
                    $location->_file(),
                    $location->_line(),
                    $location->_column()
                );
            $this->insert_relation_into(
                    $insert,
                    $location,
                    $method_reference,
                    $location->_line()
                );
        }
    }

    public function enterFunctionCall(Insert $insert, Location $location, N\Expr\FuncCall $node)
    {
        // Omit calls to closures, we would not be able to
        // analyze them anyway atm.
        // Omit functions in arrays, we would not be able to
        // analyze them anyway atm.
        if (!($node->name instanceof N\Expr\Variable ||
              $node->name instanceof N\Expr\ArrayDimFetch)) {
            $function_reference = $insert->_function_reference(
                    $node->name->parts[0],
                    $location->_file(),
                    $location->_line(),
                    $location->_column()
                );
            $this->insert_relation_into(
                    $insert,
                    $location,
                    $function_reference
                );
        }
    }

    public function enterGlobal(Insert $insert, Location $location, N\Stmt\Global_ $node)
    {
        foreach ($node->vars as $var) {
            if (!($var instanceof N\Expr\Variable) || !is_string($var->name)) {
                throw new \RuntimeException(
                    "Expected Variable with string name, found: " . print_r($var, true)
                );
            }
            $global = $insert->_global($var->name);
            $this->insert_relation_into(
                    $insert,
                    $location,
                    $global
                );
        }
    }

    public function enterArrayDimFetch(Insert $insert, Location $location, N\Expr\ArrayDimFetch $node)
    {
        if ($node->var instanceof N\Expr\Variable
        && $node->var->name == "GLOBALS"
        // Ignore usage of $GLOBALS with variable index.
        && !($node->dim instanceof N\Expr\Variable)) {
            $global = $insert->_global($node->dim->value);
            $this->insert_relation_into(
                    $insert,
                    $location,
                    $global
                );
        }
    }

    public function enterErrorSuppress(Insert $insert, Location $location, N\Expr\ErrorSuppress $node)
    {
        $language_construct = $insert->_language_construct("@");
        $this->insert_relation_into(
                $insert,
                $location,
                $language_construct
            );
    }
}
