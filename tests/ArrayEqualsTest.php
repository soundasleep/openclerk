<?php

require_once(__DIR__ . "/../inc/global.php");

class ArrayEqualsTest extends PHPUnit_Framework_TestCase {

  function test1() {
    $this->assertTrue(array_equals(array(1, 2), array(2, 1)));
    $this->assertFalse(array_equals(array(1, 2), array(1, 2, 3)));
  }

  function test2() {
    $this->assertTrue(array_equals(array(1, 2, 1), array(2, 1, 1)));
    $this->assertFalse(array_equals(array(1, 2, 1), array(1, 2, 3)));
  }

  function test3() {
    $this->assertTrue(array_equals(array('foo' => 'a', 'bar' => 'b'), array(1 => 'b', 'key' => 'a')));
  }

  function testEmpty() {
    $this->assertTrue(array_equals(array(), array()));
    $this->assertFalse(array_equals(array('a'), array()));
    $this->assertFalse(array_equals(array(), array('b')));
  }

}
