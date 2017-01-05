<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Report;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Configuration for the app and engine.
 */
class Config implements ConfigurationInterface {
    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var Report\Config[]|null
     */
    protected $reports = null;

    /**
     * Build the configuration from nested arrays using a processor.
     *
     * @param   string  $path
     */
    public function __construct($path, array $values) {
        assert('is_string($path)');
        if (substr($path, strlen($path) - 1, 1) == "/") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        $this->path = $path;
        $processor = new \Symfony\Component\Config\Definition\Processor();
        $this->values = $processor->processConfiguration($this, $values);
    }

    /**
     * Definition of configuration for symfony.
     *
     * @inheritdocs
     */
    public function getConfigTreeBuilder() {
        $tree_builder = new TreeBuilder();
        $root = $tree_builder->root("dicto");
        $c = $root->children();
        $this->add_project_node($c);
        $this->add_analysis_node($c);
        $this->add_rules_node($c);
        $this->add_runtime_node($c);
        $this->add_reports_node($c);
        $c->end();
        return $tree_builder;
    }

    protected function add_project_node($c) {
        $c->arrayNode("project")
            ->children()
                ->scalarNode("root")
                    ->isRequired()
                ->end()
                ->scalarNode("storage")
                    ->defaultValue(".")
                ->end()
                ->scalarNode("rules")
                    ->isRequired()
                ->end()
            ->end()
        ->end();
    }

    protected function add_analysis_node($c) {
        $c->arrayNode("analysis")
            ->children()
                ->arrayNode("ignore")
                    ->prototype("scalar")
                    ->end()
                    ->defaultValue([])
                ->end()
                ->booleanNode("store_index")
                    ->defaultValue(false)
                ->end()
                ->booleanNode("store_results")
                    ->defaultValue(true)
                ->end()
            ->end()
            ->addDefaultsIfNotSet()
        ->end();
    }

    protected function add_rules_node($c) {
        $c->arrayNode("rules")
            ->children()
                ->arrayNode("schemas")
                    ->prototype("scalar")
                    ->end()
                    ->defaultValue
                        ([\Lechimp\Dicto\Rules\DependOn::class
                        , \Lechimp\Dicto\Rules\Invoke::class
                        , \Lechimp\Dicto\Rules\ContainText::class
                        ])
                ->end()
                ->arrayNode("properties")
                    ->prototype("scalar")
                    ->end()
                    ->defaultValue
                        ([\Lechimp\Dicto\Variables\Name::class
                        , \Lechimp\Dicto\Variables\In::class
                        ])
                ->end()
                ->arrayNode("variables")
                    ->prototype("scalar")
                    ->end()
                    ->defaultValue
                        ([\Lechimp\Dicto\Variables\Namespaces::class
                        , \Lechimp\Dicto\Variables\Classes::class
                        , \Lechimp\Dicto\Variables\Interfaces::class
                        , \Lechimp\Dicto\Variables\Traits::class
                        , \Lechimp\Dicto\Variables\Functions::class
                        , \Lechimp\Dicto\Variables\Globals::class
                        , \Lechimp\Dicto\Variables\Files::class
                        , \Lechimp\Dicto\Variables\Methods::class
                        , \Lechimp\Dicto\Variables\ErrorSuppressor::class
                        , \Lechimp\Dicto\Variables\Exit_::class
                        , \Lechimp\Dicto\Variables\Die_::class
                        , \Lechimp\Dicto\Variables\Eval_::class
                        ])
                ->end()
            ->end()
            ->addDefaultsIfNotSet()
        ->end();
    }

    protected function add_runtime_node($c) {
        $c->arrayNode("runtime")
            ->children()
                ->booleanNode("check_assertions")
                    ->defaultValue(false)
                ->end()
            ->end()
            ->addDefaultsIfNotSet()
        ->end();
    }

    protected function add_reports_node($c) {
        $c->arrayNode("reports")
            ->prototype("array")
                ->children()
                    ->scalarNode("name")
                    ->defaultValue(null)
                ->end()
                ->scalarNode("class")
                    ->isRequired()
                ->end()
                ->scalarNode("target")
                    ->defaultValue(null)
                ->end()
                ->scalarNode("source")
                    ->defaultValue(null)
                ->end()
                ->variableNode("config")
                    ->defaultValue([])
                ->end()
            ->end()
        ->end();
    }

    /**
     * @return  string
     */
    public function path() {
        return $this->path;
    }

    protected function maybe_prepend_path($path) {
        assert('is_string($path)');
        if (substr($path, 0, 2) === "./") {
            return $this->path()."/".substr($path, 2);
        }
        return $path;
    }

    /**
     * @return  string
     */
    public function project_rules() {
        return $this->maybe_prepend_path($this->values["project"]["rules"]);
    }

    /**
     * @return  string
     */
    public function project_root() {
        return $this->maybe_prepend_path($this->values["project"]["root"]);
    }

    /**
     * @return  string
     */
    public function project_storage() {
        return $this->maybe_prepend_path($this->values["project"]["storage"]);
    }

    /**
     * @return  string[]
     */
    public function analysis_ignore() {
        return $this->values["analysis"]["ignore"];
    }

    /**
     * @return  bool
     */
    public function analysis_store_index() {
        return $this->values["analysis"]["store_index"];
    }

    /**
     * @return  bool
     */
    public function analysis_store_results() {
        return $this->values["analysis"]["store_results"];
    }

    /**
     * @return  string[]
     */
    public function rules_schemas() {
        return $this->values["rules"]["schemas"];
    }

    /**
     * @return  string[]
     */
    public function rules_properties() {
        return $this->values["rules"]["properties"];
    }

    /**
     * @return  string[]
     */
    public function rules_variables() {
        return $this->values["rules"]["variables"];
    }

    /**
     * @return  bool
     */
    public function runtime_check_assertions() {
        return $this->values["runtime"]["check_assertions"];
    }

    /**
     * @return Report\Config[]
     */
    public function reports() {
        if ($this->reports !== null) {
            return $this->reports;
        }
        $this->reports = [];
        foreach ($this->values["reports"] as $rep) {
            $this->reports[] = new Report\Config
                ( $this->path()
                , $rep["class"]
                , $rep["target"]
                , $rep["config"]
                , $rep["name"]
                , $rep["source"]
                );
        }
        return $this->reports;
    }
}
