<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Regexp;

class RegexpTest extends PHPUnit_Framework_TestCase {
    public function regexp($str) {
        return new Regexp($str);
    }

    public function test_raw() {
        $re = $this->regexp("ab");
        $this->assertEquals("ab", $re->raw());
    }

    public function test_throws_on_delim() {
        try {
            $this->regexp("%");
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_match() {
        $re = $this->regexp("ab");
        $this->assertTrue($re->match("ab"));
        $this->assertFalse($re->match("abc"));
        $this->assertFalse($re->match("0abc"));
        $this->assertFalse($re->match("0ab"));
        $this->assertFalse($re->match("cd"));
    }

    public function test_match_beginning() {
        $re = $this->regexp("ab");
        $this->assertTrue($re->match_beginning("ab"));
        $this->assertTrue($re->match_beginning("abc"));
        $this->assertFalse($re->match_beginning("0abc"));
        $this->assertFalse($re->match_beginning("0ab"));
        $this->assertFalse($re->match_beginning("cd"));
    }

    public function test_search() {
        $re = $this->regexp("ab");
        $this->assertTrue($re->search("ab"));
        $this->assertTrue($re->search("abc"));
        $this->assertTrue($re->search("0abc"));
        $this->assertTrue($re->search("0ab"));
        $this->assertFalse($re->search("cd"));
    }

    public function test_matches() {
        $re = $this->regexp("(a)(b)");

        $matches = array();
        $re->match("ab", false, $matches);
        $this->assertEquals(["ab", "a", "b"], $matches);

        $matches = array();
        $re->match_beginning("ab", false, $matches);
        $this->assertEquals(["ab", "a", "b"], $matches);

        $matches = array();
        $re->search("ab", false, $matches);
        $this->assertEquals(["ab", "a", "b"], $matches);
    }

    public function test_dotall() {
        $re = $this->regexp("(a).(b)");

        $this->assertFalse($re->match("a\nb"));
        $this->assertFalse($re->match_beginning("a\nb"));
        $this->assertFalse($re->search("a\nb"));

        $this->assertTrue($re->match("a\nb", true));
        $this->assertTrue($re->match_beginning("a\nb", true));
        $this->assertTrue($re->search("a\nb", true));
    }
}
