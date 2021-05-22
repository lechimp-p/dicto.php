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

use Lechimp\Dicto\Regexp;
use Psr\Log\LoggerInterface as Log;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;
use Lechimp\Flightcontrol\Flightcontrol;
use Lechimp\Flightcontrol\File;
use Lechimp\Flightcontrol\FSObject;

/**
 * Creates an index of source files.
 */
class Indexer
{
    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Insert
     */
    protected $insert;

    /**
     * @var \PhpParser\Parser
     */
    protected $parser;

    /**
     * @var ASTVisitor[]
     */
    protected $ast_visitors;

    public function __construct(Log $log, \PhpParser\Parser $parser, Insert $insert, array $ast_visitors)
    {
        $this->log = $log;
        $this->parser = $parser;
        $this->insert = $insert;
        $this->ast_visitors = array_map(function (ASTVisitor $v) {
            return $v;
        }, $ast_visitors);
    }

    /**
     * Index a directory.
     *
     * @param   string  $path
     * @param   array   $ignore_paths
     * @return  null
     */
    public function index_directory($path, array $ignore_paths)
    {
        $ignore_paths_re = array_map(function ($ignore) {
            return new Regexp($ignore);
        }, $ignore_paths);
        $fc = $this->init_flightcontrol($path);
        $fc->directory("/")
            ->recurseOn()
            ->filter(function (FSObject $obj) use (&$ignore_paths_re) {
                foreach ($ignore_paths_re as $re) {
                    if ($re->match($obj->path())) {
                        return false;
                    }
                }
                return true;
            })
            ->foldFiles(null, function ($_, File $file) use ($path) {
                try {
                    $this->index_file($path, $file->path());
                } catch (\PhpParser\Error $e) {
                    $this->log->error("in " . $file->path() . ": " . $e->getMessage());
                }
            });
    }

    /**
     * Initialize the filesystem abstraction.
     *
     * @return  Flightcontrol
     */
    public function init_flightcontrol($path)
    {
        $adapter = new LocalFilesystemAdapter(realpath($path), null, LOCK_EX, LocalFilesystemAdapter::SKIP_LINKS);
        $flysystem = new Filesystem($adapter);
        return new Flightcontrol($flysystem);
    }

    /**
     * @param   string  $base_dir
     * @param   string  $path
     * @return  null
     */
    public function index_file($base_dir, $path)
    {
        assert('is_string($base_dir)');
        assert('is_string($path)');
        $this->log->info("indexing: " . $path);
        $full_path = "$base_dir/$path";
        $content = file_get_contents($full_path);
        if ($content === false) {
            throw \InvalidArgumentException("Can't read file $path.");
        }
        $this->index_content($path, $content);
    }

    /**
     * @param   string  $path
     * @param   string  $content
     * @return  null
     */
    public function index_content($path, $content)
    {
        assert('is_string($path)');
        assert('is_string($content)');

        $stmts = $this->parser->parse($content);
        if ($stmts === null) {
            throw new \RuntimeException("Can't parse file $path.");
        }

        $traverser = new \PhpParser\NodeTraverser;
        $location = new LocationImpl($path, $content);
        $visitor = new BaseVisitor($location, $this->insert);
        $traverser->addVisitor($visitor);
        foreach ($this->ast_visitors as $visitor) {
            $traverser->addVisitor(new AdapterVisitor($location, $this->insert, $visitor));
        }
        $traverser->traverse($stmts);
    }
}
