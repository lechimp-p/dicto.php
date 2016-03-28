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

/**
 * PDepend Analyzer/ReportGenerator for an Invoke rule.
 */
class ViolationsReport implements \PDepend\Report\ReportGenerator
                                , \PDepend\Report\CodeAwareGenerator {
    /**
     * @var &array
     */
    protected $violations;

    /**
     * @var \PDepend\Metrics\Analyzer[]
     */
    protected $analyzers;

    /**
     * @var \PDepend\Source\AST\ASTArtifactList|null
     */
    protected $artifacts;

    /**
     * @var CompiledRules
     */
    protected $compiled_rules;

    public function __construct(array &$violations, CompiledRules $compiled_rules) {
        $this->violations = &$violations;
        $this->compiled_rules = $compiled_rules;
        $this->analyzers = array(); 
        $this->artifacts = null;
    }

    /**
     * @inheritdoc
     */
    public function log(\PDepend\Metrics\Analyzer $analyzer) {
        throw new \RuntimeError("This report does not expect to log any analyzers.");
    }

    /**
     * @inheritdoc
     */
    public function getAcceptedAnalyzers() {
        // We do not request any analyzers from the dependency injection nor do
        // we accept any.
        return array();
    }

    /**
     * @inheritdoc
     */
    public function close() {
        assert('!is_null($this->artifacts)');

        $this->compiled_rules->setViolationsArray($this->violations);
        foreach($this->artifacts as $artifact) {
            $artifact->accept($this->compiled_rules);
        }
        $this->compiled_rules->rmViolationsArray();
    }

    /**
     * @inheritdoc
     */
    public function setArtifacts(\PDepend\Source\AST\ASTArtifactList $artifacts) {
        $this->artifacts = $artifacts;
    }
} 
