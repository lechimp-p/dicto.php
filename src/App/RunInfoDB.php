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
 * Database to store information about one run of the program.
 */
interface RunInfoDB {
    /**
     * @return null
     */
    public function save(RunInfo $info);

    /**
     * @return RunInfo
     */
    public function get_current();
}
