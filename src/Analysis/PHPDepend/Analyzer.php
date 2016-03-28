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
 * Interface to a facility that analyses a codebase.
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
        $res = $engine->analyze();
        print_r($res);

        $violations = array(); 


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
