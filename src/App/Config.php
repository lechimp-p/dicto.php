<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
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
     * @var array
     */
    protected $values;

    /**
     * Build the configuration from nested arrays using a processor.
     */
    public function __construct(array $values) {
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
        $root
            ->children()
                ->arrayNode("project")
                    ->children()
                        ->scalarNode("root")
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("sqlite")
                    ->children()
                        ->booleanNode("memory")
                            ->defaultValue(true)
                        ->end()
                        ->scalarNode("path")
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("analysis")
                    ->children()
                        ->arrayNode("ignore")
                            ->prototype("scalar")
                        ->end()
                    ->end()
            ->end()
        ->end();

        return $tree_builder;
    }

    /**
     * @return  string
     */
    public function project_root() {
        return $this->values["project"]["root"];
    }

    /**
     * @return  bool 
     */
    public function sqlite_memory() {
        return $this->values["sqlite"]["memory"];
    }

    /**
     * @return  string|null
     */
    public function sqlite_path() {
        return $this->values["sqlite"]["path"];
    }

    /**
     * @return  string[]
     */
    public function analysis_ignore() {
        return $this->values["analysis"]["ignore"];
    }
}
