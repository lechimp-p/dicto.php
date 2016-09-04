<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto as Dicto;
use Lechimp\Dicto\Rules as R;
use Lechimp\Dicto\Variables as V;
use Lechimp\Dicto\App\IndexDB;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Indexer\Indexer;
use Doctrine\DBAL\DriverManager;
use PhpParser\ParserFactory;
use Psr\Log\LogLevel;

require_once(__DIR__."/LoggerMock.php");
require_once(__DIR__."/ReportGeneratorMock.php");

abstract class RuleTest extends PHPUnit_Framework_TestCase {
    /**
     * @return  R\Schema
     */
    abstract public function schema();

    public function setUp() {
        $this->connection = DriverManager::getConnection
            ( array
                ( "driver" => "pdo_sqlite"
                , "memory" => true
                )
            );
        $this->db = new IndexDB($this->connection);
        $this->db->init_sqlite_regexp();
        $this->db->maybe_init_database_schema();

        $this->rp = new ReportGeneratorMock();
        $this->log = new LoggerMock();
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->indexer = new Indexer($this->log, $this->parser, $this->db);
        $this->schema()->register_listeners($this->indexer);
    }

    public function analyze(R\Rule $rule, $source) {
        $this->indexer->index_content("source.php", $source);

        $ruleset = new R\Ruleset($rule->variables(), array($rule));
        $analyzer = new Dicto\Analysis\Analyzer($this->log, $ruleset, $this->db, $this->rp);
        $analyzer->run();
        return $this->rp->violations;
    }
}
