<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Analysis\Analyzer;
use Lechimp\Dicto\Analysis\Index;
use Lechimp\Dicto\Graph\IndexQuery;
use Lechimp\Dicto\Rules as R;
use Lechimp\Dicto\Variables as V;
use Psr\Log\LogLevel;

require_once(__DIR__."/LoggerMock.php");
require_once(__DIR__."/ReportGeneratorMock.php");

class AnalyzerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->rp = new ReportGeneratorMock();
        $this->log = new LoggerMock();
        $this->query_mocks = [];
        $this->index = $this
            ->getMockBuilder(Index::class)
            ->setMethods(["query"])
            ->getMock();
        $this->index
            ->method("query")
            ->will($this->returnCallback(function() {
                $methods =
                    [ "expand"
                    , "extract"
                    , "filter"
                    , "run"
                    , "filter_by_types"
                    , "files"
                    , "classes"
                    , "methods"
                    , "functions"
                    , "expand_relations"
                    , "expand_target"
                    ];
                $mock = $this
                    ->getMockBuilder(IndexQuery::class)
                    ->setMethods($methods)
                    ->getMock();
                foreach ($methods as $method) {
                    if ($method == "run") {
                        continue;
                    }
                    $mock->method($method)->willReturn($mock);
                }
                $mock->method("run")->willReturn([]);
                $this->query_mocks[] = $mock;
                return $mock;
            }));
    }

    public function test_logging() {
        $rule1 = new R\Rule
            ( R\Rule::MODE_CANNOT
            , new V\Classes("allClasses")
            , new R\ContainText()
            , array("foo")
            );
        $rule2 = new R\Rule
            ( R\Rule::MODE_CANNOT
            , new V\Functions("allFunctions")
            , new R\DependOn()
            , array(new V\Methods("allMethods"))
            );
        $vars = array_merge($rule1->variables(), $rule2->variables());

        $ruleset = new R\Ruleset($vars, array($rule1, $rule2));
        $analyzer = new Analyzer($this->log, $ruleset, $this->index, $this->rp);
        $analyzer->run();

        $expected = array(LogLevel::INFO, "checking: ".$rule1->pprint(), array());
        $this->assertContains($expected, $this->log->log);

        $expected = array(LogLevel::INFO, "checking: ".$rule2->pprint(), array());
        $this->assertContains($expected, $this->log->log);
    }
}
