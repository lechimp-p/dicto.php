<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Indexer\PhpParser;

use Lechimp\Dicto\Indexer as I;
use Lechimp\Dicto\Analysis\Consts;
use PhpParser\Node as N;

/**
 * Implementation of Indexer with PhpParser.
 */
class Indexer implements I\Indexer,  \PhpParser\NodeVisitor {
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

    // state for parsing a file

    /**
     * @var bool
     */
    protected $currently_parsing;

    /**
     * @var string|null
     */
    protected $file_path = null;

    /**
     * @var string[]|null
     */
    protected $file_content = null;


    public function __construct(\PhpParser\Parser $parser) {
        $this->project_root_path = "";
        $this->parser = $parser;
        $this->currently_parsing = false;
    }

    /**
     * @inheritdoc
     */
    public function index_file($path) {
        assert('$this->insert !== null');
        assert('!$this->currently_parsing');

        $content = file_get_contents($this->project_root_path."/$path");
        if ($content === false) {
            throw \InvalidArgumentException("Can't read file $path.");
        }

        $stmts = $this->parser->parse($content);

        $traverser = new \PhpParser\NodeTraverser;
        $traverser->addVisitor($this);

        $this->init_parsing_state($path, $content);
        $traverser->traverse($stmts);
        $this->deinit_parsing_state();
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

    // read and write of state during parsing a file.

    private function init_parsing_state($path, $content) {
        assert('!$this->currently_parsing');
        assert('is_string($path)');
        assert('is_string($content)');
        $this->currently_parsing = true;
        $this->file_path = $path;
        $this->file_content = explode("\n", $content);
    }

    private function deinit_parsing_state() {
        assert('$this->currently_parsing');
        $this->currently_parsing = false;
    }


    private function lines_from_to($start, $end) {
        assert('is_int($start)');
        assert('is_int($end)');
        return implode("\n", array_slice($this->file_content, $start-1, $end-$start+1));
    }

    // handlers for different actions the parser needs to take

    private function insert_entity_information(\PhpParser\Node $node) {
        assert('$this->currently_parsing');

        $start_line = $node->getAttribute("startLine");
        $end_line = $node->getAttribute("endLine");
        $source = $this->lines_from_to($start_line, $end_line);

        if ($node instanceof N\Stmt\Class_) {
            $this->insert->entity
                ( Consts::CLASS_ENTITY
                , $node->name
                , $this->file_path
                , $start_line
                , $end_line
                , $source
                );
        } 
        elseif ($node instanceof N\Stmt\ClassMethod
                or $node instanceof N\Stmt\Function_) {
            $this->insert->entity
                ( Consts::FUNCTION_ENTITY
                , $node->name
                , $this->file_path
                , $start_line
                , $end_line
                , $source
                );
        }
    }

    // from \PhpParser\NodeVisitor

    /**
     * @inheritdoc
     */
    public function beforeTraverse(array $nodes) {
        assert('$this->currently_parsing');

        // for sure found a file
        $this->insert->entity
            ( Consts::FILE_ENTITY
            , $this->file_path
            , $this->file_path
            , 1
            , count($this->file_content)
            , implode("\n", $this->file_content)
            );

        return null;
    }

    /**
     * @inheritdoc
     */
    public function afterTraverse(array $nodes) {
        assert('$this->currently_parsing');
        return null;
    }

    /**
     * @inheritdoc
     */
    public function enterNode(\PhpParser\Node $node) {
        assert('$this->currently_parsing');
        $this->insert_entity_information($node);
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(\PhpParser\Node $node) {
        assert('$this->currently_parsing');
    }
}
