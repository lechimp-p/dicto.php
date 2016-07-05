<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\App\SourceStatusGit;

class SourceStatusTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->repo_path = __DIR__."/..";
    }

    public function source_status_git() {
        return new SourceStatusGit($this->repo_path);
    }

    public function test_correct_commit_hash() {
        $escaped_repo_path = escapeshellarg($this->repo_path);
        $command = "git -C $escaped_repo_path rev-parse HEAD";

        exec($command, $expected, $returned);

        $this->assertEquals(0, $returned, implode("\n", $expected));

        $source_status = $this->source_status_git();

        $this->assertInternalType("string", $source_status->commit_hash());
        $this->assertEquals($expected[0], $source_status->commit_hash());       
    }
}
