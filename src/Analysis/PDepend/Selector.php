<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Analysis\PDepend;

use Lechimp\Dicto\Analysis as Ana;
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Definition\Variables as Vars;
use Lechimp\Dicto\Definition\Rules as Rules;

use PDepend\Source\AST as AST;
use PDepend\Source\ASTVisitor\AbstractASTVisitor;

/**
 * Helps to select some parts of the ast.
 */
class Selector extends AbstractASTVisitor { 
    public function __construct() {
        $this->invocations = null;
    }

    public function invocations_in(AST\ASTArtifact $artifact) {
        assert('is_null($this->invocations)');
        $this->invocations = array();

        $artifact->accept($this);

        $invocations = $this->invocations;
        $this->invocations = null;
        return $invocations;
    }

    public function visitMethod(AST\ASTMethod $method) {
        parent::visitMethod($method);
        if (!is_null($this->invocations)) {
            $this->search_invocations($method);
        }
    }

    public function visitFunction(AST\ASTFunction $function) {
        parent::visitFunction($function);
        if (!is_null($this->invocations)) {
            $this->search_invocations($method);
        }
    }

    protected function search_invocations(AST\AbstractASTCallable $callable) {
        $invocation_type = "PDepend\\Source\\AST\\ASTInvocation";
        $identifier_type = "PDepend\\Source\\AST\\ASTIdentifier";
        $new_invocations = array_map(function(AST\ASTInvocation $invocation) use ($identifier_type) {
            return $invocation->getFirstChildOfType($identifier_type);
        }, $callable->findChildrenOfType($invocation_type));
        $this->invocations = array_merge($this->invocations, $new_invocations); 
    }
} 
