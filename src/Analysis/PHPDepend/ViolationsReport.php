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

    public function __construct(array &$violations) {
        $this->violations = &$violations;
        $this->analyzers = array(); 
        $this->artifacts = null;
    }

    /**
     * @inheritdoc
     */
    public function log(\PDepend\Metrics\Analyzer $analyzer) {
        $this->analyzers[] = $analyzer;
    }

    /**
     * @inheritdoc
     */
    public function close() {
        assert('!is_null($this->artifacts)');

        // TODO: this is very inefficient, there is a loop hidden in analyze.
        // i need to replace this with something smarter. Looks a little as
        // if i would not need the analyzers at all.
        foreach ($this->analyzers as $analyzer) {
            $analyzer->analyze($this->artifacts);
        }
    }

    /**
     * @inheritdoc
     */
    public function getAcceptedAnalyzers() {
        // We do not request any analyzers from the dependency injection.
        return array();
    }

    /**
     * @inheritdoc
     */
    public function setArtifacts(\PDepend\Source\AST\ASTArtifactList $artifacts) {
        $this->artifacts = $artifacts;
    }
} 
