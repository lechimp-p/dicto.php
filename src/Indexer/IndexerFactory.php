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

use Psr\Log\LoggerInterface as Log;

/**
 * Creates Indexers.
 */
class IndexerFactory {
    /**
     * @var Log
     */
    protected $log;

    /**
     * @var \PhpParser\Parser
     */
    protected $parser;

    /**
     * @var string
     */
    protected $project_root_path;

    public function __construct(Log $log, \PhpParser\Parser $parser, $project_root_path) {
        $this->log = $log;
        $this->parser = $parser;
        assert('is_string($project_root_path)');
        $this->project_root_path = $project_root_path;
    }

    /**
     * @return  Indexer
     */
    public function build(Insert $insert, array $schemas) {
        $indexer = new Indexer
            ( $this->log
            , $this->parser
            , $this->project_root_path
            , $insert
            );
        foreach ($schemas as $schema) {
            assert('$schema instanceof \Lechimp\Dicto\Rules\Schema');
            $schema->register_listeners($indexer);
        }
        return $indexer;
    }
}
