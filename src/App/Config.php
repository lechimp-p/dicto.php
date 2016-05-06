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
     * @var string
     */
    protected $project_root;

    /**
     * @var bool 
     */
    protected $sqlite_memory; 

    /**
     * @var string|null
     */
    protected $sqlite_path; 

    /**
     * @var string[]
     */
    protected $analysis_ignore;

    /**
     * Build the configuration from a nested array.
     */
    public function __construct(array $params) {
            
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
        return $this->analysis_ignore();
    }
}
