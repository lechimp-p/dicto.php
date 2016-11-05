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
     * Store a file in the database.
     *
     * @param   string  $path
     * @param   string  $source
     * @return  mixed   handle to the file
     */
    public function _file($path, $source);

    /**
     * Store a namespace in the database.
     *
     * @param   string  $name
     * @return  mixed   handle to the namespace
     */
    public function _namespace($name);

    /**
     * Store a class in the database.
     *
     * @param   string      $name
     * @param   mixed       $file       handle from _file
     * @param   int         $start_line
     * @param   int         $end_line
     * @param   mixed|null  $namespace  handle from namespace
     * @return  mixed       handle to the class
     */
    public function _class($name, $file, $start_line, $end_line, $namespace = null);

    /**
     * Store an interface in the database.
     *
     * @param   string  $name
     * @param   mixed       $file       handle from _file
     * @param   int         $start_line
     * @param   int         $end_line
     * @param   mixed|null  $namespace  handle from namespace
     * @return  mixed       handle to the class
     */
    public function _interface($name, $file, $start_line, $end_line, $namespace = null);

    /**
     * Store a trait in the database.
     *
     * @param   string  $name
     * @param   mixed       $file       handle from _file
     * @param   int         $start_line
     * @param   int         $end_line
     * @param   mixed|null  $namespace  handle from namespace
     * @return  mixed       handle to the class
     */
    public function _trait($name, $file, $start_line, $end_line, $namespace = null);

    /**
     * Store a method in the database.
     *
     * @param   string  $name
     * @param   mixed   $class  handle from _class
     * @param   mixed   $file   handle from _file
     * @param   int     $start_line
     * @param   int     $end_line
     * @return  mixed   handle to the method
     */
    public function _method($name, $class, $file, $start_line, $end_line);

    /**
     * Store a function in the database.
     *
     * @param   string      $name
     * @param   mixed       $file       handle from _file
     * @param   int         $start_line
     * @param   int         $end_line
     * @param   mixed|null  $namespace  handle from namespace
     * @return  mixed       handle to the function
     */
    public function _function($name, $file, $start_line, $end_line, $namespace = null);

    /**
     * Store information about the usage of a global to the database.
     * If there already is such a global just returns the handle.
     *
     * @param   string  $name
     * @return  mixed   handle to the global
     */
    public function _global($name);

    /**
     * Store information about the usage of a language construct to the database.
     * If there already is such a language construct just returns the handle.
     *
     * @param   string  $name
     * @return  mixed   handle to the language construct
     */
    public function _language_construct($name);

    /**
     * Store information about a reference to a method to the database.
     *
     * @param   string  $name
     * @param   mixed   $file   handle from _file
     * @param   int     $line
     * @param   int     $column
     * @return  mixed   handle to the method reference
     */
    public function _method_reference($name, $file, $line, $column);

    /**
     * Store information about a reference to a function to the database.
     *
     * @param   string  $name
     * @param   mixed   $file   handle from _file
     * @param   int     $line
     * @param   int     $column
     * @return  mixed   handle to the function reference
     */
    public function _function_reference($name, $file, $line, $column);

    /**
     * Store the fact, that two entity have a relation, established at a
     * certain source code location.
     *
     * @param   mixed       $left_entity    handle from some other insert method
     * @param   string      $relation
     * @param   mixed       $right_entity   handle from some other insert method
     * @param   mixed       $file           handle from _file
     * @param   int         $line
     * @return  null
     */
    public function _relation($left_entity, $relation, $right_entity, $file, $line);
}
