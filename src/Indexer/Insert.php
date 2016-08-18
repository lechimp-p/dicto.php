<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Indexer;

use Lechimp\Dicto\Analysis\Variable;

/**
 * This is how to insert new entries in the index. 
 */
interface Insert {
    /**
     * Store the name or just get the id if name already exists.
     *
     * @param   string      $name
     * @param   string      $type
     * @return  int
     */
    public function name($name, $type);

    /**
     * Store a filename or just get its id if the file is already stored.
     *
     * @param   string      $path
     * @return  int
     */
    public function file($path);

    /**
     * Store some source code of file.
     *
     * @param   string      $path
     * @param   string      $content
     * @return  null 
     */
    public function source($path, $content);

    /**
     * Store the location of some name definition.
     *
     * The source code for the location must have been inserted before.
     *
     * @param   string      $name
     * @param   string      $type
     * @param   string      $file
     * @param   int         $start_line
     * @param   int         $end_line
     * @return  null 
     */
    public function definition($name, $type, $file, $start_line, $end_line);

    /**
     * Store the fact, that two names have a relation, established at a
     * certain source code location.
     *
     * The names must have been inserted before.
     * The source code for the location must have been inserted before.
     *
     * @param   string      $name_left
     * @param   string      $name_right
     * @param   string      $which
     * @param   string      $file
     * @param   int         $line
     * @return  null
     */
     public function relation($name_left, $name_right, $which, $file, $line);
}
