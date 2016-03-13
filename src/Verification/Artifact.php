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
 * Basic interface to a source code artifact.
 */
interface Artifact {
    /**
     * The name of the artifact.
     *
     * @return  string
     */
    public function name();

    /**
     * Get all dependencies of the artifact.
     *
     * @return  Artifact[]
     */
    public function dependencies();

    /**
     * Get all things that this artifact invokes.
     *
     * @return  Artifact[]
     */
    public function invocations();

    /**
     * Get the source code of this artifact.
     *
     * @return  string
     */
    public function source();

    /**
     * Get the file this artifact is contained in.
     *
     * @return  FileArtifact 
     */
    public function file();

    /**
     * Get the line in the file the definition of this artifact starts.
     *
     * @return  int
     */
    public function start_line();

    /**
     * Get the line in the file the definition of this artifact ends.
     *
     * @return  int
     */
    public function end_line();
}
