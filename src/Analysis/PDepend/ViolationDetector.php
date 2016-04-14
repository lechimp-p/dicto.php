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

use PDepend\Source\AST as AST;
use PDepend\Source\ASTVisitor as ASTVisitor;


/**
 * Detects violations of a ruleset in an PDepend AST
 */
class ViolationDetector implements ASTVisitor\ASTVisitor { 
    /**
     * @var Def\Ruleset
     */
    protected $ruleset;

    /**
     * @var Ana\Violation|null
     */
    protected $violations;

    public function __construct(Def\Ruleset $ruleset) {
        $this->ruleset = $ruleset;
        $this->violations = null;
    }

    /**
     * Retrieve the violations in a given PDepend AST.
     *
     * @param   AST\ASTArtifact|AST\ASTArtifactList $ast
     * @return  Ana\Violation[] 
     */
    public function violations_in($ast) {
        if ($ast instanceof AST\ASTArtifactList) {
            $res = array();
            foreach($ast as $namespace) {
                $res[] = $this->violations_in($namespace);
            }
            return call_user_func_array("array_merge", $res);
        }
        else {
            assert('$ast instanceof \\PDepend\\Source\\AST\\ASTArtifact');
            assert('$this->violations === null');
            $this->violations = array();
            $ast->accept($this);
            $violations = $this->violations;;
            $this->violations = null; 
            return $violations;
        }
    }

    /**
     * @inheritdoc
     */
    public function addVisitListener(ASTVisitor\ASTVisitListener $listener) {
        throw new \LogicException("LSP violation: This class won't take any VisitListeners.");
    }

    /**
     * @inheritdoc
     */
    public function visitClass(AST\ASTClass $class) {
        // Some rules might be interested in classes.
        throw new \Exception("Go on here...");
    }

    /**
     * @inheritdoc
     */
    public function visitTrait(AST\ASTTrait $trait) {
        // No interest in this at current state of rules...
    }

    /**
     * @inheritdoc
     */
    public function visitCompilationUnit(AST\ASTCompilationUnit $compilationUnit) {
        // Some rules might be interested in files.
    } 

    /**
     * @inheritdoc
     */
    public function visitFunction(AST\ASTFunction $function) {
        // Some rules might be interested in functions.
    } 

    /**
     * @inheritdoc
     */
    public function visitInterface(AST\ASTInterface $interface) {
        // No interest in this at current state of rules...
    }

    /**
     * @inheritdoc
     */
    public function visitMethod(AST\ASTMethod $method) {
        // Some rules might be interested in methods.
    } 

    /**
     * @inheritdoc
     */
    public function visitNamespace(AST\ASTNamespace $namespace) {
        // Just traverse this...
        foreach($namespace->getClasses() as $class) {
            $class->accept($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function visitParameter(AST\ASTParameter $parameter) {
        // No interest in this at current state of rules...
    }

    /**
     * @inheritdoc
     */
    public function visitProperty(AST\ASTProperty $property) {
        // No interest in this at current state of rules...
    }

    /**
     * @inheritdoc
     */
    public function __call($method, $args) {
        // Shameless dup from PDepend\Source\ASTVisitor\AbstractASTVisitor
        if (!isset($args[1])) {
            throw new \RuntimeException("No node to visit provided for $method.");
        }
        $value = $args[1];
        foreach ($args[0]->getChildren() as $child) {
            $value = $child->accept($this, $value);
        }
        return $value;
    }
} 
