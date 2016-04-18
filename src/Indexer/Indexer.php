<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Indexer;

/**
 * This is what an indexer is supposed to do.
 */
interface Indexer {
    /**
     * Index a file by and insert it to some database defined via set_insert.
     *
     * @param   string  $path
     * @return  null
     */
    public function index_file($path);

    /**
     * Tell the indexer which insert to use.
     *
     * @param   Insert  $insert
     * @return  null
     */
    public function use_insert(Insert $insert);

    /**
     * Tell the indexer where the root of the project is located. He should
     * use it to truncate $paths in index_file.
     *
     * @param   string  $path
     * @return  null
     */
    public function set_project_root_to($path);
}
