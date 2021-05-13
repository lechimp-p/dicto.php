<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Indexer;

/**
 * Provides information on the location where something was detected.
 */
interface Location {
    /**
     * Get the handle to the file where the location is in.
     *
     * @return  mixed
     */
    public function _file();

    /**
     * Get the handle to the namespace the location is in.
     *
     * @return mixed|null
     */
    public function _namespace();

    /**
     * Get a handle to the class, interface or trait the location is in.
     *
     * @return mixed|null
     */
    public function _class_interface_trait();

    /**
     * Get a handle to the function or method the location is in.
     *
     * @return mixed|null
     */
    public function _function_method();

    /**
     * @return  int
     */
    public function _line();

    /**
     * @return  int
     */
    public function _column();
}

