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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

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
        $violations = array();
        $report = new ViolationsReport($violations);
        foreach ($this->ruleset->rules() as $rule) {
            $this->add_rule($report, $violations, $rule);
        }
        $engine->addReportGenerator($report);
        $engine->analyze();

        return new Ana\Result($this->ruleset, $violations);
    }

    protected function add_rule(ViolationsReport $report, array &$violations, Def\Rules\Rule $rule) {
        $cls = get_class($rule);
        switch ($cls) {
            case "Lechimp\\Dicto\\Definition\\Rules\\Invoke":
                $analyzer = new InvokeAnalyzer();
                $analyzer->setRule($rule);
                $analyzer->setViolationsArray($violations);
                break;
            default:
                throw new \UnexpectedValueException("Cannot add rule of type $cls");
        }
        $report->log($analyzer);
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
        return new \PDepend\Metrics\AnalyzerFactory($this->get_di_container());
    }

    protected function get_di_container() {
        // stripped down version of \PDepend\Application::createContainer
        $extensions = array();
        $params = array();

        $container= new ContainerBuilder(new ParameterBag($params));

        $container->compile();
        return $container; 
    }
} 
