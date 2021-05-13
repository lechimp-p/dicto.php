<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\App;

/**
 * Get the current state of the sourcecode by using git.
 */
class SourceStatusGit implements SourceStatus {
    /**
     * @var string
     */
    protected $path;

    /**
     * @param   string  $path
     */
    public function __construct($path) {
        assert('is_string($path)');
        $this->path = $path;
    }

    /**
     * @inheritdoc
     */
    public function commit_hash() {
        $escaped_path = escapeshellarg($this->path);
        $command = "git -C $escaped_path rev-parse HEAD";
        exec($command, $output, $returned);
        if ($returned !== 0) {
            throw new \RuntimeException(implode("\n", $output));
        }
        return $output[0];
    }
}
