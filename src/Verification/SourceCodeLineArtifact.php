<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Verification;

/**
 * A line of source code in a file.
 */
class SourceCodeLineArtifact implements Artifact {
    /**
     * @var FileArtifact 
     */
    private $file;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $source;

    public function __construct(FileArtifact $file, $line, $source) {
        assert('is_int($line)');
        assert('is_string($source)');
        $this->file = $file;
        $this->line = $line;
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    public function name() {
        return ""; 
    }

    /**
     * @inheritdoc
     */
    public function dependencies() {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function invocations() {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function source() {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function file() {
        return $this->file;
    }

    /**
     * @inheritdoc
     */
    public function start_line() {
        return $this->line;
    }

    /**
     * @inheritdoc
     */
    public function end_line() {
        return $this->line;
    }
}
