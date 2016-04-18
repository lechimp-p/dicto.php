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
     * @var string
     */
    protected $project_root_path;

    /**
     * @var I\Insert|null
     */
    protected $insert;

    /**
     * @var \PhpParser\Parser
     */
    protected $parser;


    public function __construct(\PhpParser\Parser $parser) {
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function index_file($path) {
        assert('$this->insert !== null');
        $content = file_get_contents($path);
        if ($content === false) {
            throw \InvalidArgumentException("Can't read file $path.");
        }
        $stmts = $this->parser->parse($content);
    }

    /**
     * @inheritdoc
     */
    public function use_insert(I\Insert $insert) {
        $this->insert = $insert;
    }

    /**
     * @inheritdoc
     */
    public function set_project_root_to($path) {
        assert('is_string($path)');
        $this->project_root_path = $path;
    }
}
