<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;

class DictoClassTest extends PHPUnit_Framework_TestCase {
    public function tearDown() {
        Dicto::discardDefinition();
    }

    public function test_only_one_definition_once() {
        Dicto::startDefinition();
        try {
            Dicto::startDefinition();
            $this->assertFalse("This should not happen.");
        }
        catch (\RuntimeException $e) {}
    }

    public function test_only_end_definition_once() {
        Dicto::startDefinition();
        Dicto::endDefinition();
        try {
            Dicto::endDefinition();
            $this->assertFalse("This should not happen.");
        }
        catch (\RuntimeException $e) {}
    }

    public function test_no_var_def_outside_definition() {
        try {
            Dicto::Foo();
            $this->assertFalse("This should not happen.");
        }
        catch (\RuntimeException $e) {}
    }

    public function test_raise_on_args_to_var_def() {
        try {
            Dicto::startDefinition();
            Dicto::Foo("bar");
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {}
    }

    public function test_no_unfinished_var_decl() {
        try {
            Dicto::startDefinition();
            Dicto::Foo();
            Dicto::Foo();
            $this->assertFalse("This should not happen.");
        }
        catch (\RuntimeException $e) {}
    }

    public function test_throw_on_unknown_var_in_var_decl() {
        try {
            Dicto::startDefinition();
            Dicto::Foo()->means()->Bar();
            $this->assertFalse("This should not happen.");
        }
        catch (\RuntimeException $e) {}
    }
}
