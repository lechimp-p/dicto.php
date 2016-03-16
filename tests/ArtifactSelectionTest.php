<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;
use Lechimp\Dicto\Verification as Ver;

class ArtifactMock {
    public function __construct($name, $deps = array(), $invocations = array(), $source = "") {
        $this->name = $name;
        $this->deps = $deps;
        $this->invocations = $invocations;
        $this->source = $source;
    }

    public function name() { return $this->name; }
    public function dependencies() { return $this->deps; }
    public function invocations() { return $this->invocations; }
    public function source() { return $this->source; }
    public function file() { return new FileMock($this->name.".src"); }
    public function start_line() { return 0; }
    public function end_line() { return count($this->source())-1; }
}

class ClassMock extends ArtifactMock implements Ver\ClassArtifact {};
class FunctionMock extends ArtifactMock implements Ver\FunctionArtifact {};
class GlobalMock extends ArtifactMock implements Ver\GlobalArtifact {};
class BuildinMock extends ArtifactMock implements Ver\BuildinArtifact {};
class FileMock extends ArtifactMock implements Ver\FileArtifact {
    public function file() { return $this; }
};

class ArtifactSelectionTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->class_one = new ClassMock("ClassOne");
        $this->class_two = new ClassMock("ClassTwo");
        $this->class_three = new ClassMock("ClsThree");
        $this->function_one = new FunctionMock("function_one");
        $this->function_two = new FunctionMock("function_two");
        $this->global_one = new GlobalMock("global_one");
        $this->global_two = new GlobalMock("global_two");
        $this->file_one = new FileMock("file_one");
        $this->file_two = new FileMock("file_two");
        $this->buildin_one = new BuildinMock("buildin_one");
        $this->buildin_two = new BuildinMock("buildin_two");

        $this->selector = new Ver\Implementation\Selector;
    }

    public function get_var($definition) {
        Dicto::startDefinition();
        $definition();
        $defs = Dicto::endDefinition();
        $variables = $defs->variables();

        return array_pop($variables); 
    }

    public function test_match_every_class() {
        $var = $this->get_var(function() { 
            Dicto::allClasses()->means()->classes();
        });

        $this->assertTrue($this->selector->matches($var, $this->class_one));
        $this->assertFalse($this->selector->matches($var, $this->function_one));
        $this->assertFalse($this->selector->matches($var, $this->global_one));
        $this->assertFalse($this->selector->matches($var, $this->file_one));
        $this->assertFalse($this->selector->matches($var, $this->buildin_one));

        $this->assertTrue($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));
    }

    public function test_match_every_function() {
        $var = $this->get_var(function() { 
            Dicto::allFunctions()->means()->functions();
        });

        $this->assertFalse($this->selector->matches($var, $this->class_one));
        $this->assertTrue($this->selector->matches($var, $this->function_one));
        $this->assertFalse($this->selector->matches($var, $this->global_one));
        $this->assertFalse($this->selector->matches($var, $this->file_one));
        $this->assertFalse($this->selector->matches($var, $this->buildin_one));

        $this->assertFalse($this->selector->matches($var, $this->class_two));
        $this->assertTrue($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));
    }

    public function test_match_every_global() {
        $var = $this->get_var(function() { 
            Dicto::allGlobals()->means()->globals();
        });

        $this->assertFalse($this->selector->matches($var, $this->class_one));
        $this->assertFalse($this->selector->matches($var, $this->function_one));
        $this->assertTrue($this->selector->matches($var, $this->global_one));
        $this->assertFalse($this->selector->matches($var, $this->file_one));
        $this->assertFalse($this->selector->matches($var, $this->buildin_one));

        $this->assertFalse($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertTrue($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));
    }

    public function test_match_every_file() {
        $var = $this->get_var(function() { 
            Dicto::allFiles()->means()->files();
        });

        $this->assertFalse($this->selector->matches($var, $this->class_one));
        $this->assertFalse($this->selector->matches($var, $this->function_one));
        $this->assertFalse($this->selector->matches($var, $this->global_one));
        $this->assertTrue($this->selector->matches($var, $this->file_one));
        $this->assertFalse($this->selector->matches($var, $this->buildin_one));

        $this->assertFalse($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertTrue($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));
    }

    public function test_match_every_buildin() {
        $var = $this->get_var(function() { 
            Dicto::allBuildins()->means()->buildins();
        });

        $this->assertFalse($this->selector->matches($var, $this->class_one));
        $this->assertFalse($this->selector->matches($var, $this->function_one));
        $this->assertFalse($this->selector->matches($var, $this->global_one));
        $this->assertFalse($this->selector->matches($var, $this->file_one));
        $this->assertTrue($this->selector->matches($var, $this->buildin_one));

        $this->assertFalse($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertTrue($this->selector->matches($var, $this->buildin_two));
    }

    public function test_match_every_class_with_name() {
        $var = $this->get_var(function() { 
            Dicto::OneClasses()->means()->classes()->with()->name(".*One");
        });

        $this->assertTrue($this->selector->matches($var, $this->class_one));
        $this->assertFalse($this->selector->matches($var, $this->function_one));
        $this->assertFalse($this->selector->matches($var, $this->global_one));
        $this->assertFalse($this->selector->matches($var, $this->file_one));
        $this->assertFalse($this->selector->matches($var, $this->buildin_one));

        $this->assertFalse($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));
    }

    public function test_match_every_function_with_name() {
        $var = $this->get_var(function() { 
            Dicto::OneFunctions()->means()->functions()->with()->name(".*one");
        });

        $this->assertFalse($this->selector->matches($var, $this->class_one));
        $this->assertTrue($this->selector->matches($var, $this->function_one));
        $this->assertFalse($this->selector->matches($var, $this->global_one));
        $this->assertFalse($this->selector->matches($var, $this->file_one));
        $this->assertFalse($this->selector->matches($var, $this->buildin_one));

        $this->assertFalse($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));
    }

    public function test_match_every_global_with_name() {
        $var = $this->get_var(function() { 
            Dicto::OneGlobals()->means()->globals()->with()->name(".*one");
        });

        $this->assertFalse($this->selector->matches($var, $this->class_one));
        $this->assertFalse($this->selector->matches($var, $this->function_one));
        $this->assertTrue($this->selector->matches($var, $this->global_one));
        $this->assertFalse($this->selector->matches($var, $this->file_one));
        $this->assertFalse($this->selector->matches($var, $this->buildin_one));

        $this->assertFalse($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));
    }

    public function test_match_every_file_with_name() {
        $var = $this->get_var(function() { 
            Dicto::OneFiles()->means()->files()->with()->name(".*one");
        });

        $this->assertFalse($this->selector->matches($var, $this->class_one));
        $this->assertFalse($this->selector->matches($var, $this->function_one));
        $this->assertFalse($this->selector->matches($var, $this->global_one));
        $this->assertTrue($this->selector->matches($var, $this->file_one));
        $this->assertFalse($this->selector->matches($var, $this->buildin_one));

        $this->assertFalse($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));
    }

    public function test_match_every_buildin_with_name() {
        $var = $this->get_var(function() { 
            Dicto::OneBuildins()->means()->buildins()->with()->name(".*one");
        });

        $this->assertFalse($this->selector->matches($var, $this->class_one));
        $this->assertFalse($this->selector->matches($var, $this->function_one));
        $this->assertFalse($this->selector->matches($var, $this->global_one));
        $this->assertFalse($this->selector->matches($var, $this->file_one));
        $this->assertTrue($this->selector->matches($var, $this->buildin_one));

        $this->assertFalse($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));
    }

    public function test_match_two_classes_and() {
        $var = $this->get_var(function() { 
            Dicto::namedClasses1()->means()->classes()->with()->name("Class.*");
            Dicto::namedClasses2()->means()->classes()->with()->name(".*One");
            Dicto::namedClasses3()->means()->namedClasses1()->as_well_as()->namedClasses2();
        });

        $this->assertTrue($this->selector->matches($var, $this->class_one));
        $this->assertFalse($this->selector->matches($var, $this->function_one));
        $this->assertFalse($this->selector->matches($var, $this->global_one));
        $this->assertFalse($this->selector->matches($var, $this->file_one));
        $this->assertFalse($this->selector->matches($var, $this->buildin_one));

        $this->assertTrue($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));

        $this->assertFalse($this->selector->matches($var, $this->class_three));
    }

    public function test_match_two_classes_except() {
        $var = $this->get_var(function() { 
            Dicto::namedClasses1()->means()->classes()->with()->name("Class.*");
            Dicto::namedClasses2()->means()->classes()->with()->name(".*One");
            Dicto::namedClasses3()->means()->namedClasses1()->but_not()->namedClasses2();
        });

        $this->assertFalse($this->selector->matches($var, $this->class_one));
        $this->assertFalse($this->selector->matches($var, $this->function_one));
        $this->assertFalse($this->selector->matches($var, $this->global_one));
        $this->assertFalse($this->selector->matches($var, $this->file_one));
        $this->assertFalse($this->selector->matches($var, $this->buildin_one));

        $this->assertTrue($this->selector->matches($var, $this->class_two));
        $this->assertFalse($this->selector->matches($var, $this->function_two));
        $this->assertFalse($this->selector->matches($var, $this->global_two));
        $this->assertFalse($this->selector->matches($var, $this->file_two));
        $this->assertFalse($this->selector->matches($var, $this->buildin_two));

        $this->assertFalse($this->selector->matches($var, $this->class_three));
    }
}
