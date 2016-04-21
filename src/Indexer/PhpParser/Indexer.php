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

    /**
     * @var int[]|null
     */
    protected $dependent_entity_ids = null;

    /**
     * @var int[]|null
     */
    protected $invoker_entity_ids = null;

    /**
     * This contains the stack of ids were currently in, i.e. the nesting of
     * known code blocks we are in.
     *
     * @var int[]|null
     */
    protected $entity_id_stack = null;


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
        $this-> dependent_entity_ids = array();
        $this->invoker_entity_ids = array();
    }

    private function deinit_parsing_state() {
        assert('$this->currently_parsing');
        $this->currently_parsing = false;
        $this->file_path = null;
        $this->file_content = null;
        $this->dependent_entity_id = null;
        $this->invoker_entity_ids = null;
        $this->entity_id_stack = null;
    }


    private function lines_from_to($start, $end) {
        assert('is_int($start)');
        assert('is_int($end)');
        return implode("\n", array_slice($this->file_content, $start-1, $end-$start+1));
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

        $start_line = $node->getAttribute("startLine");
        $end_line = $node->getAttribute("endLine");
        $source = $this->lines_from_to($start_line, $end_line);

        $id = null;

        // Class
        if ($node instanceof N\Stmt\Class_) {
            $id = $this->insert->entity
                ( Consts::CLASS_ENTITY
                , $node->name
                , $this->file_path
                , $start_line
                , $end_line
                , $source
                );

            // Every interesting reference we find will create a dependency
            // on this class.
            $this->dependent_entity_ids[] = $id;

            // Every invocation of a reference we find will be a invocation
            // in this class.
            $this->invoker_entity_ids[] = $id;
        }
        // Method or Function
        elseif ($node instanceof N\Stmt\ClassMethod
                or $node instanceof N\Stmt\Function_) {
            $id = $this->insert->entity
                ( Consts::FUNCTION_ENTITY
                , $node->name
                , $this->file_path
                , $start_line
                , $end_line
                , $source
                );

            // Every interesting reference we find will create a dependency
            // on this method or function.
            $this->dependent_entity_ids[] = $id;

            // Every invocation of a reference we find will be a invocation
            // in this method or function.
            $this->invoker_entity_ids[] = $id;
        }
        // Call to method or function
        elseif ($node instanceof N\Expr\MethodCall
                or $node instanceof N\Expr\FuncCall) {
            // TODO: we might need to implode parts if we know a thing about
            // namespaces?
            $name = $node->name->parts[0];

            $ref_id = $this->insert->reference
                ( Consts::FUNCTION_ENTITY
                , $name
                , $this->file_path
                , $node->name->getAttribute("startLine")
                );

            $start_line = $node->getAttribute("startLine");
            $source_line = $this->lines_from_to($start_line, $start_line);
            // We are in a class a need to record the dependency now.
            foreach ($this->dependent_entity_ids as $dependent_id) {
                $this->insert->dependency
                    ( $dependent_id
                    , $ref_id
                    , $this->file_path
                    , $start_line
                    , $source
                    );
            }
            // We also need to record a invocation in every invoking
            // entity now.
            foreach ($this->invoker_entity_ids as $invoker_id) {
                $this->insert->invocation
                    ( $invoker_id
                    , $ref_id
                    , $this->file_path
                    , $start_line
                    , $source
                    );
            }
        }

        if ($id !== null) {
            $this->entity_id_stack[] = $id;
        }
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(\PhpParser\Node $node) {
        assert('$this->currently_parsing');

        // Class
        if ($node instanceof N\Stmt\Class_) {
            // We pushed it, we need to pop it now, as we are leaving the class.
            $id = array_pop($this->entity_id_stack);

            // Done with the dependencies of this class.
            assert('in_array($id, $this->dependent_entity_ids)');
            $key = array_search($id, $this->dependent_entity_ids);
            unset($this->dependent_entity_ids[$key]);

            // Done with the invocations in this method or function.
            assert('in_array($id, $this->invoker_entity_ids)');
            $key = array_search($id, $this->invoker_entity_ids);
            unset($this->invoker_entity_ids[$key]);
        }
        // Method or Function
        elseif ($node instanceof N\Stmt\ClassMethod
                or $node instanceof N\Stmt\Function_) {
            // We pushed it, we need to pop it now, as we are leaving the method
            // or function.
            $id = array_pop($this->entity_id_stack);

            // Done with the dependencies of this method or function.
            assert('in_array($id, $this->dependent_entity_ids)');
            $key = array_search($id, $this->dependent_entity_ids);
            unset($this->dependent_entity_ids[$key]);

            // Done with the invocations in this method or function.
            assert('in_array($id, $this->invoker_entity_ids)');
            $key = array_search($id, $this->invoker_entity_ids);
            unset($this->invoker_entity_ids[$key]);
        }
    }
}
