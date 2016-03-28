<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Analysis\PHPDepend;

use Lechimp\Dicto\Analysis as Ana;
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Definition\Variables as Vars;
use Lechimp\Dicto\Definition\Rules as Rules;

use PDepend\Source\AST as AST;
use PDepend\Source\ASTVisitor\AbstractASTVisitor;

/**
 * PDepend Analyzer/ReportGenerator for an Invoke rule.
 */
class CompiledRules extends AbstractASTVisitor { 
    /**
     * @var array
     */
    protected $rules;

    /**
     * @var &array|null
     */
    protected $violations;

    /**
     * @var Selector
     */
    protected $selector;

    public function __construct(Def\Ruleset $ruleset) {
        $this->rules = $ruleset->rules();
        $this->violations = null;
        $this->selector = new Selector;
    }

    public function setViolationsArray(array &$violations) {
        assert('is_null($this->violations)');
        $this->violations = &$violations;
    }

    public function rmViolationsArray() {
        assert('!is_null($this->violations)');
        unset($this->violations);
        $this->violations = null;
    }

    public function visitClass(AST\ASTClass $class) {
        foreach ($this->rules as $rule) {
            // TODO: Take rule mode into account here.
            if ($this->matches($rule->subject(), $class)) {
                $this->check_rule($class, $rule);
            }
        }
    }

    protected function matches(Vars\Variable $var, $artifact) {
        if ($var instanceof Vars\Classes && $artifact instanceof AST\ASTClass) {
            return true;
        }
        elseif ($var instanceof Vars\WithName) {
           return $this->matches($var->variable(), $artifact)
               && $this->matches_name($var->regexp(), $artifact); 
        }
        print_r($artifact);
        return false;
    }

    protected function matches_name($regexp, $artifact) {
        if ($artifact instanceof AST\ASTIdentifier) {
            $name = $artifact->getImage();
        }
        else {
            if (!method_exists($artifact, "getName")) {
                return false;
            }
            $name = $artifact->getName();
        }
        return preg_match("%$regexp%s", $name) === 1;
    }

    protected function check_rule(AST\AbstractASTArtifact $artifact, Rules\Rule $rule) {
        if ($rule instanceof Rules\Invoke) {
            $invocations = $this->selector->invocations_in($artifact);

            $mode = $rule->mode();
            if ($mode == Rules\Rule::MODE_MUST) {
                $found_it = false;
                foreach ($invocations as $invocation) {
                    $invokes = $rule->invokes();
                    if ($invokes instanceof Vars\WithName) {
                        if ($this->matches_name($invokes->regexp(), $invocation)) {
                            $found_it = true;
                            break;
                        }
                    }
                }
                if (!$found_it) {
                    $this->add_violation($artifact, $rule);
                }
            }
            elseif ($mode == Rules\Rule::MODE_CANNOT || $mode == Rules\Rule::MODE_ONLY_CAN) {
                foreach ($invocations as $invocation) {
                    $invokes = $rule->invokes();
                    if ($invokes instanceof Rules\WithName) {
                        if ($this->matches_name($invokes->regexp(), $invocation)) {
                            $this->add_violation($artifact, $rule);
                        }
                    }
                }
            }
        }
        else {
            throw new \UnexpectedValueException("Can't check rule of type ".get_class($rule));
        } 
    }

    protected function add_violation($artifact, Rules\Rule $rule) {
        $file = $artifact->getCompilationUnit();
        $file_source = explode("\n", $file->getSource());
        $this->violations[] = new Ana\Violation
                                    ( $rule 
                                    , $file->getFileName()
                                    , $artifact->getStartLine()
                                    , $file_source[$artifact->getStartLine()-1]
                                    , array() // TODO
                                    , array() // TODO
                                    ); 
    }
} 
