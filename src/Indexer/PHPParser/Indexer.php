<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Indexer\PHPParser;

use Lechimp\Dicto\Indexer as I;

/**
 * Implementation of Indexer with PHPParser.
 */
class Indexer implements I\Indexer {
    /**
     * @inheritdoc
     */
    public function index_file($path) {
    }

    /**
     * @inheritdoc
     */
    public function use_insert(I\Insert $insert) {
    }

    /**
     * @inheritdoc
     */
    public function set_project_root_to($path) {
    }
}
