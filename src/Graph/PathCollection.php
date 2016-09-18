<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

/**
 * A collection of paths.
 */
class PathCollection {
    /**
     * @var Path[]
     */
    protected $paths;

    /**
     * @param   Path[]
     */
    public function __construct(array $paths) {
        $this->paths = array_map(function(Path $p) {
            return $p;
        }, $paths);
    }

    /**
     * Expand every path by applying the given function which creates new paths
     * from it.
     *
     * @param   \Closure    Path -> Path[]
     * @return  null
     */
    public function extend(\Closure $extend) {
        $new_paths = []; 
        foreach ($this->paths as $path) {
            array_map(function(Path $p) use (&$new_paths) { 
                $new_paths[] = $p;
            }, $extend($path));
        }
        $this->paths = $new_paths;
    }

    /**
     * Filter the contained paths by a matcher on the last entity.
     *
     * @param   Matcher $matcher
     * @return  null 
     */
    public function filter_by_last_entity(Matcher $matcher) {
        $new_paths = [];
        foreach ($this->paths as $path) {
            if ($matcher->matches($path->last())) {
                $new_paths[] = $path;
            }
        }
        $this->paths = $new_paths;
    }

    /**
     * Get the contained paths.
     *
     * @return Path[]
     */
    public function paths() {
        return $this->paths;
    }

    /**
     * Is the collection empty?
     *
     * @return  bool
     */
    public function is_empty() {
        return count($this->paths) === 0;
    }
}
