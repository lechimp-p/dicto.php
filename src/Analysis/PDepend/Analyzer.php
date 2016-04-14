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


/**
 * Implementation of Analyzer using PDepend.
 */
class Analyzer implements Ana\Analyzer {
    /**
     * @var Def\Ruleset
     */
    protected $ruleset;

    public function __construct(Def\Ruleset $ruleset) {
        $this->ruleset = $ruleset;
    }

    /**
     * @inheritdoc
     */
    public static function instantiate_for(Def\Ruleset $ruleset) {
        return new Analyzer($ruleset);
    }

    /**
     * @inheritdoc
     */
    public function run_analysis_on($src) {
        assert('is_string($src)');

        $engine = $this->get_engine(); 
        $engine->addDirectory($src);
        $ast = $engine->analyze();

        $di = $this->build_di_container();
        $detector = $di["violation_detector"];
        $violations = $detector->violations_in($ast);

        return new Ana\Result($this->ruleset, $violations);
    }

    protected function get_engine() {
        return new \PDepend\Engine
            ( $this->get_engine_config()
            , $this->get_cache_factory()
            , $this->get_analyzer_factory()
            );
    }

    protected function get_engine_config() {
        $config = new \StdClass();

        $config->parser = new \StdClass();
        $config->parser->nesting = 65536; // maximum nesting level 
        
        return new \PDepend\Util\Configuration($config);
    }

    protected function get_cache_factory() {
        $config = new \StdClass();

        $config->cache = new \StdClass();
        // TODO: replace this by a FileCacheDriver
        $config->cache->driver = "memory";

        return new \PDepend\Util\Cache\CacheFactory
            ( new \PDepend\Util\Configuration($config) );
    }

    protected function get_analyzer_factory() {
        return new AnalyzerFactory();
    }

    protected function get_violations_in(AST\ASTArtifactList $namespaces) {
        $res = array();
        return $res;
    }

    protected function build_di_container() {
        $di = new \Pimple\Container(); 

        $di["violation_detector"] = function($c) {
            return new ViolationDetector($this->ruleset);
        };

        return $di;
    }
} 
