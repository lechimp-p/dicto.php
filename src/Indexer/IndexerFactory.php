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
use Lechimp\Dicto\Rules\Schema;

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

    /**
     * @var Schema[]
     */
    protected $schemas;

    /**
     * @param   string      $project_root_path
     * @param   Schema[]    $schemas
     */
    public function __construct(Log $log, \PhpParser\Parser $parser, $project_root_path, array $schemas) {
        $this->log = $log;
        $this->parser = $parser;
        assert('is_string($project_root_path)');
        $this->project_root_path = $project_root_path;
        $this->schemas = array_map(function(Schema $s) { return $s; }, $schemas);
    }

    /**
     * @return  Indexer
     */
    public function build(Insert $insert) {
        $indexer = new Indexer
            ( $this->log
            , $this->parser
            , $this->project_root_path
            , $insert
            );
        foreach ($this->schemas as $schema) {
            assert('$schema instanceof \Lechimp\Dicto\Rules\Schema');
            $schema->register_listeners($indexer);
        }
        return $indexer;
    }
}
