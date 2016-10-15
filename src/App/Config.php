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
     * @var array
     */
    protected $defaults =
        [ "analysis" =>
            [ "ignore"  => []
            , "store_index" => false
            ]
        , "rules" =>
            [ "schemas" =>
                [ \Lechimp\Dicto\Rules\DependOn::class
                , \Lechimp\Dicto\Rules\Invoke::class
                , \Lechimp\Dicto\Rules\ContainText::class
                ]
            , "properties" =>
                [ \Lechimp\Dicto\Variables\Name::class
                , \Lechimp\Dicto\Variables\In::class
                ]
            , "variables" =>
                [ \Lechimp\Dicto\Variables\Classes::class
                , \Lechimp\Dicto\Variables\Functions::class
                , \Lechimp\Dicto\Variables\Globals::class
                , \Lechimp\Dicto\Variables\Files::class
                , \Lechimp\Dicto\Variables\Methods::class
                , \Lechimp\Dicto\Variables\ErrorSuppressor::class
                , \Lechimp\Dicto\Variables\Exit_::class
                , \Lechimp\Dicto\Variables\Die_::class
                ]
            ]
        , "runtime" =>
            [ "check_assertions" => false
            ]
        ];

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
        $values = array_merge([$this->defaults], $values);
        $this->values = $processor->processConfiguration($this, $values);
    }

    /**
     * Definition of configuration for symfony.
     *
     * @inheritdocs
     */
    public function getConfigTreeBuilder() {
        // TODO: maybe change definition in a way that does not append
        //       to rules.*-arrays
        $tree_builder = new TreeBuilder();
        $root = $tree_builder->root("dicto");
        $root
            ->children()
                ->arrayNode("project")
                    ->children()
                        ->scalarNode("root")
                            ->isRequired()
                        ->end()
                        ->scalarNode("storage")
                            ->isRequired()
                        ->end()
                        ->scalarNode("rules")
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("analysis")
                    ->children()
                        ->arrayNode("ignore")
                            ->prototype("scalar")
                            ->end()
                            ->isRequired()
                        ->end()
                        ->booleanNode("store_index")
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("rules")
                    ->children()
                        ->arrayNode("schemas")
                            ->prototype("scalar")
                            ->end()
                            ->isRequired()
                        ->end()
                        ->arrayNode("properties")
                            ->prototype("scalar")
                            ->end()
                            ->isRequired()
                        ->end()
                        ->arrayNode("variables")
                            ->prototype("scalar")
                            ->end()
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("runtime")
                    ->children()
                        ->booleanNode("check_assertions")
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $tree_builder;
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
}
