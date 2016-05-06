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

/**
 * Configuration for the app and engine.
 */
class Config {
    /**
     * @var string|null
     */
    protected $project_root = null;

    /**
     * @var bool|null
     */
    protected $sqlite_memory = null;

    /**
     * @var string|null
     */
    protected $sqlite_path = null;

    /**
     * @var string[]
     */
    protected $analysis_ignore = array();

    /**
     * Build the configuration from a nested array.
     */
    public function __construct(array $params) {
        if (isset($params["project"])) {
            $project = $params["project"];
            assert('is_array($project)');
            if (isset($project["root"])) {
                $this->project_root = $params["project"]["root"];
                assert('is_string($this->project_root)');
            }
        }

        if (isset($params["sqlite"])) {
            $sqlite = $params["sqlite"];
            assert('is_array($sqlite)');
            if (isset($sqlite["memory"])) {
                $this->sqlite_memory = $sqlite["memory"];
                assert('is_bool($this->sqlite_memory)');
            }
            if (isset($sqlite["path"])) {
                $this->sqlite_path = $sqlite["path"];
                assert('is_string($this->sqlite_path)');
            }
        }

        if (isset($params["analysis"])) {
            $analysis = $params["analysis"];
            assert('is_array($analysis)');
            if (isset($analysis["ignore"])) {
                assert('is_array($analysis["ignore"])');
                $this->analysis_ignore = array_map(function($s) {
                    assert('is_string($s)');
                    return $s;
                }, $analysis["ignore"]);
            }
        }
    }

    /**
     * @return  string
     */
    public function project_root() {
        return $this->project_root;
    }

    /**
     * @return  bool 
     */
    public function sqlite_memory() {
        return $this->sqlite_memory;
    }

    /**
     * @return  string|null
     */
    public function sqlite_path() {
        return $this->sqlite_path;
    }

    /**
     * @return  string[]
     */
    public function analysis_ignore() {
        return $this->analysis_ignore;
    }
}
