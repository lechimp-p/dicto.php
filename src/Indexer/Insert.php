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
     * Store a class in the database.
     *
     * @param   string  $name
     * @param   mixed   $file   handle from _file
     * @param   int     $start_line
     * @param   int     $end_line
     * @return  mixed   handle to the class
     */
    public function _class($name, $file, $start_line, $end_line);

    /**
     * Store a method in the database.
     *
     * @param   string  $name
     * @param   mixed   $class  handle from _class
     * @param   mixed   $file   handle from _file
     * @param   int     $start_line
     * @param   int     $end_line
     * @return  null
     */
    public function _method($name, $class, $file, $start_line, $end_line);

    /**
     * Store a function in the database.
     *
     * @param   string  $name
     * @param   mixed   $file   handle from _file
     * @param   int     $start_line
     * @param   int     $end_line
     * @return  null
     */
    public function _function($name, $file, $start_line, $end_line);

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
     * If there already is such a reference just returns the handle.
     *
     * @param   string  $name
     * @param   mixed   $file   handle from _file
     * @param   int     $line
     * @return  mixed   handle to the method reference
     */
    public function _method_reference($name, $file, $line);

    /**
     * Store information about a reference to a function to the database.
     * If there already is such a reference just returns the handle.
     *
     * @param   string  $name
     * @param   mixed   $file   handle from _file
     * @param   int     $line
     * @return  mixed   handle to the function reference
     */
    public function _function_reference($name, $file, $line);

    /**
     * Store the fact, that two entity have a relation, established at a
     * certain source code location.
     *
     * @param   mixed       $left_entity    handle from some other insert method
     * @param   mixed       $right_entity   handle from some other insert method
     * @param   mixed       $file           handle from _file
     * @param   int         $line
     * @return  null
     */
    public function _relation($left_entity, $right_entity, $file, $line);

    //interface
    //namespace

    /**
     * Store the name or just get the id if name already exists.
     *
     * @param   string      $name
     * @param   string      $type
     * @return  int
     */
//    public function name($name, $type);

    /**
     * Store a filename or just get its id if the file is already stored.
     *
     * @param   string      $path
     * @return  int
     */
//    public function file($path);

    /**
     * Store some source code of file.
     *
     * @param   string      $path
     * @param   string      $content
     * @return  int         id of file 
     */
//    public function source($path, $content);

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
     * @return  int[]       (id of name, id of definition)
     */
//    public function definition($name, $type, $file, $start_line, $end_line);

    /**
     * Store some info about a method.
     *
     * @param   int         $name_id
     * @param   int         $class_name_id
     * @param   int         $definition_id
     * @return  null
     */
//    public function method_info($name_id, $class_name_id, $definition_id);

    /**
     * Store the fact, that two names have a relation, established at a
     * certain source code location.
     *
     * The names must have been inserted before.
     * The source code for the location must have been inserted before.
     *
     * @param   int         $name_left_id
     * @param   int         $name_right_id
     * @param   string      $which
     * @param   string      $file
     * @param   int         $line
     * @return  null
     */
//     public function relation($name_left_id, $name_right_id, $which, $file, $line);
}
