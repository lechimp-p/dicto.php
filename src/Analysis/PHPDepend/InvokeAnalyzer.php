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

use PDepend\Source\AST as AST;

/**
 * PDepend Analyzer/ReportGenerator for an Invoke rule.
 */
class InvokeAnalyzer extends \PDepend\Metrics\AbstractAnalyzer {
    /**
     * @var Def\Rules\Rule
     */
    protected $rule = null;

    /**
     * @var &array
     */
    protected $violations = null;

    /**
     * @inheritdoc
     */
    public function analyze($namespaces) {
        assert('!is_null($this->rule)');
        assert('!is_null($this->violations)');

        $this->fireStartAnalyzer();
        foreach ($namespaces as $namespace) {
            $namespace->accept($this);
        }
        $this->fireEndAnalyzer();
    }

    /**
     * @param   Def\Rules\Rule    $rule
     */
    public function setRule(Def\Rules\Rule $rule) {
        $this->rule = $rule;
    }

    /**
     * @param   &array  $violations
     */
    public function setViolationsArray(array &$violations) {
        $this->violations = &$violations;
    }

    public function visitClass(AST\ASTClass $cls) {
        print_r($cls->getName());
    }
} 
