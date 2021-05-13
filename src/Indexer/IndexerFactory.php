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

use Lechimp\Dicto\Rules\Schema;
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
     * @var Schema[]
     */
    protected $schemas;

    /**
     * @var ASTVisitor[]
     */
    protected $ast_visitors;

    /**
     * @param   Schema[]    $schemas
     */
    public function __construct(Log $log, \PhpParser\Parser $parser, array $schemas) {
        $this->log = $log;
        $this->parser = $parser;
        $this->ast_visitors = [];
        $this->schemas = array_map(function(Schema $s) {
            if ($s instanceof ASTVisitor) {
                $this->ast_visitors[] = $s;
            }
            return $s;
        }, $schemas);
    }

    /**
     * @return  Indexer
     */
    public function build(Insert $insert) {
        $indexer = new Indexer
            ( $this->log
            , $this->parser
            , $insert
            , $this->ast_visitors
            );
        return $indexer;
    }
}
