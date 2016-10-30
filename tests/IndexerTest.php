<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto;

require_once(__DIR__."/IndexerExpectations.php");

class IndexerTest extends PHPUnit_Framework_TestCase {
    use IndexerExpectations;

    public function test_file_empty() {
        $source = <<<PHP
<?php
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn(23);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_class_definition() {
        $source = <<<PHP
<?php

class AClass {
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn(23);
        $this->expect_class($insert_mock, "AClass", 23, 3, 4)
            ->willReturn(42);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_method_definition() {
        $source = <<<PHP
<?php

class AClass {
    public function a_method() {
    }
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn(23);
        $this->expect_class($insert_mock, "AClass", 23, 3, 6)
            ->willReturn(42);
        $this->expect_method($insert_mock, "a_method", 42, 23, 4, 5)
            ->willReturn(1234);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_function_definition() {
        $source = <<<PHP
<?php

function a_function() {
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn(23);
        $this->expect_function($insert_mock, "a_function", 23, 3, 4)
            ->willReturn(42);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_interface_definition() {
        $source = <<<PHP
<?php

interface AnInterface {
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn(23);
        $this->expect_interface($insert_mock, "AnInterface", 23, 3, 4)
            ->willReturn(42);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_method_in_interface() {
        $source = <<<PHP
<?php

interface AnInterface {
    public function a_method() {
    }
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn(23);
        $this->expect_interface($insert_mock, "AnInterface", 23, 3, 6)
            ->willReturn(42);
        $this->expect_method($insert_mock, "a_method", 42, 23, 4, 5)
            ->willReturn(1234);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_trait_definition() {
        $source = <<<PHP
<?php

trait ATrait {
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn("file23");
        $this->expect_trait($insert_mock, "ATrait", "file23", 3, 4)
            ->willReturn(42);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_method_in_trait() {
        $source = <<<PHP
<?php

trait ATrait {
    public function a_method() {
    }
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn("file23");
        $this->expect_trait($insert_mock, "ATrait", "file23", 3, 6)
            ->willReturn("trait42");
        $this->expect_method($insert_mock, "a_method", "trait42", "file23", 4, 5)
            ->willReturn(1234);

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_class_in_namespace_1() {
        $source = <<<PHP
<?php
namespace SomeNamespace;

class AClass{
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn("file23");
        $this->expect_namespace($insert_mock, "SomeNamespace")
            ->willReturn("namespace123");
        $this->expect_class($insert_mock, "AClass", "file23", 4, 5, "namespace123")
            ->willReturn("class42");

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_class_in_namespace_2() {
        $source = <<<PHP
<?php
namespace SomeNamespace {
    class AClass{
    }
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn("file23");
        $this->expect_namespace($insert_mock, "SomeNamespace")
            ->willReturn("namespace123");
        $this->expect_class($insert_mock, "AClass", "file23", 3, 4, "namespace123")
            ->willReturn("class42");

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_interface_in_namespace() {
        $source = <<<PHP
<?php
namespace SomeNamespace;

interface AInterface {
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn("file23");
        $this->expect_namespace($insert_mock, "SomeNamespace")
            ->willReturn("namespace123");
        $this->expect_interface($insert_mock, "AInterface", "file23", 4, 5, "namespace123")
            ->willReturn("interface42");

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    public function test_trait_in_namespace() {
        $source = <<<PHP
<?php
namespace SomeNamespace;

trait ATrait {
}
PHP;
        $insert_mock = $this->getInsertMock();

        $this->expect_file($insert_mock, "source.php", $source)
            ->willReturn("file23");
        $this->expect_namespace($insert_mock, "SomeNamespace")
            ->willReturn("namespace123");
        $this->expect_trait($insert_mock, "ATrait", "file23", 4, 5, "namespace123")
            ->willReturn("trait42");

        $indexer = $this->indexer($insert_mock);
        $indexer->index_content("source.php", $source);
    }

    // TODO: Write a test on methods in classes in namespaces.
    // TODO: Write a test with a file that contains one class in a namespace
    //       and one class not in a namespace.
    // TODO: Write a test on methods in interfaces. Do they get popped from
    //       the location?
}
