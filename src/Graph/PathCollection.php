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
     * Expand the the last entity of every path by applying the given function
     * and creating one new path for every entry in the returned array, where
     * the contained entities are appended to the path. Discard paths that are
     * not extended.
     *
     * @param   \Closure    Entity -> Entity[][]
     * @return  null
     */
    public function extend(\Closure $extend) {
        $new_paths = []; 
        foreach ($this->paths as $path) {
            $last = $path->last();
            array_map(function(array $es) use (&$new_paths, $path) {
                $clone = clone $path;
                array_map(function(Entity $e) use ($clone) {
                    $clone->append($e); 
                }, $es);
                $new_paths[] = $clone;
            }, $extend($last)); 
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
