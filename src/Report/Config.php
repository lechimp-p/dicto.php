<?php

namespace Lechimp\Dicto\Report;

/**
 * Configuration for a concrete report.
 */
class Config {
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $class_name;

    /**
     * @var string
     */
    protected $target;

    /**
     * @var string|null
     */
    protected $source_path;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param   string      $name to target the report in commands etc.
     * @param   string      $class_name to be used for report creation
     * @param   string      $target path to the file to be created
     * @param   array       $config for the report class
     * @param   string|null $source_path of the report class if it needs to be loaded
     *                      explicitely
     */
    public function __construct($name, $class_name, $target, array $config, $source_path = null) {
        assert('is_string($name)');
        assert('is_string($class_name)');
        assert('is_string($target)');
        assert('is_string($source_path)');
        $this->name = $name;
        $this->class_name = $class_name;
        $this->target = $target;
        $this->config = $config;
        $this->source_path = $source_path;
    }

    /**
     * @return  string
     */
    public function name() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function class_name() {
        return $this->class_name;
    }

    /**
     * @return string
     */
    public function target() {
        return $this->target;
    }

    /**
     * @return array
     */
    public function config() {
        return $this->config;
    }

    /**
     * @return string
     */
    public function source_path() {
        return $this->source_path;
    }
}
