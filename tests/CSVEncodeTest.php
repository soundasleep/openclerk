<?php

require_once(__DIR__ . "/../inc/global.php");

require_once(__DIR__ . "/../inc/csv.php");

/**
 * Tests for {@link csv_encode()} and {@link csv_decode()}.
 */
class CSVEncodeTest extends PHPUnit_Framework_TestCase {

  function testCSVEncode() {

    $this->assertEquals("\"hello\"", csv_encode("hello"));
    $this->assertEquals("\"hello\"\"\"", csv_encode("hello\""));
    $this->assertEquals("\"hello, world\"", csv_encode("hello, world"));

  }

  function testCSVDecode() {

    $this->assertEquals("hello", csv_decode("\"hello\""));
    $this->assertEquals("hello\"", csv_decode("\"hello\"\"\""));
    $this->assertEquals("hello, world", csv_decode("\"hello, world\""));
    $this->assertEquals("hello", csv_decode("hello"));

  }

}
