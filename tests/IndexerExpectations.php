<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Indexer\Insert;
use Lechimp\Dicto\Indexer\Indexer;
use PhpParser\ParserFactory;

require_once(__DIR__."/LoggerMock.php");

trait IndexerExpectations {
    protected function indexer(Insert $insert_mock) {
        $lexer = new \PhpParser\Lexer\Emulative
            (["usedAttributes" => ["comments", "startLine", "endLine", "startFilePos"]]);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer);
        $logger_mock = new LoggerMock();
        $indexer = new Indexer
            ( $logger_mock
            , $parser
            , $insert_mock
            );
        return $indexer;
    }

    public function getInsertMock() {
        return $this
            ->getMockBuilder(Lechimp\Dicto\Indexer\Insert::class)
            ->setMethods(
                [ "_file"
                , "_namespace"
                , "_class"
                , "_interface"
                , "_trait"
                , "_method"
                , "_function"
                , "_global"
                , "_language_construct"
                , "_method_reference"
                , "_function_reference"
                , "_relation"
                ])
            ->getMock();
    }

    public function expect_file($insert_mock, $name, $source) {
        return $insert_mock
            ->expects($this->once())
            ->method("_file")
            ->with
                ( $this->equalTo($name)
                , $this->equalTo($source)
                );
    }

    public function expect_namespace($insert_mock, $name) {
        return $insert_mock
            ->expects($this->once())
            ->method("_namespace")
            ->with
                ( $this->equalTo($name)
                );
    }

    public function expect_class($insert_mock, $name, $file, $start_line, $end_line, $namespace = null) {
        $mock = $insert_mock
            ->expects($this->once())
            ->method("_class");
            if ($namespace === null) {
                return $mock
                    ->with
                        ( $this->equalTo($name)
                        , $this->equalTo($file)
                        , $this->equalTo($start_line)
                        , $this->equalTo($end_line)
                        );
            }
            else {
                return $mock
                    ->with
                        ( $this->equalTo($name)
                        , $this->equalTo($file)
                        , $this->equalTo($start_line)
                        , $this->equalTo($end_line)
                        , $this->equalTo($namespace)
                        );
            }
        }

    public function expect_interface($insert_mock, $name, $file, $start_line, $end_line) {
        return $insert_mock
            ->expects($this->once())
            ->method("_interface")
            ->with
                ( $this->equalTo($name)
                , $this->equalTo($file)
                , $this->equalTo($start_line)
                , $this->equalTo($end_line)
                );
    }

    public function expect_trait($insert_mock, $name, $file, $start_line, $end_line) {
        return $insert_mock
            ->expects($this->once())
            ->method("_trait")
            ->with
                ( $this->equalTo($name)
                , $this->equalTo($file)
                , $this->equalTo($start_line)
                , $this->equalTo($end_line)
                );
    }

    public function expect_method($insert_mock, $name, $class, $file, $start_line, $end_line) {
        return $insert_mock
            ->expects($this->once())
            ->method("_method")
            ->with
                ( $this->equalTo($name)
                , $this->equalTo($class)
                , $this->equalTo($file)
                , $this->equalTo($start_line)
                , $this->equalTo($end_line)
                );
    }

    public function expect_function($insert_mock, $name, $file, $start_line, $end_line) {
        return $insert_mock
            ->expects($this->once())
            ->method("_function")
            ->with
                ( $this->equalTo($name)
                , $this->equalTo($file)
                , $this->equalTo($start_line)
                , $this->equalTo($end_line)
                );
    }
}
