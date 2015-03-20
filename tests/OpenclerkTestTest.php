<?php

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/OpenclerkTest.php");

/**
 * Tests the  unctionality in OpenclerkTest.
 */
class OpenclerkTestTest extends OpenclerkTest {

  function testAssertMatches() {
    $this->assertMatches("#abc#", "abc");
  }

}
