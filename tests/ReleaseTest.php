<?php

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/OpenclerkTest.php");

/**
 * Tests related to the release quality of Openclerk - i.e. more like integration tests.
 */
class ReleaseTest extends OpenclerkTest {

  // All of the previous tests have been moved into OpenclerkComponentTest

  /**
   * Sanity checks for PHP's version_compare().
   */
  function testVersionCompare() {
    $this->assertEquals(-1, version_compare("0.1", "0.2"), "0.1 < 0.2");
    $this->assertEquals(1, version_compare("0.2", "0.1"), "0.2 > 0.1");
    $this->assertEquals(0, version_compare("0.1", "0.1"), "0.1 = 0.1");
    $this->assertEquals(0, version_compare("0.12", "0.12"));
    $this->assertEquals(1, version_compare("0.12", "0.1"));
    $this->assertEquals(1, version_compare("0.12", "0.2"));
    $this->assertEquals(1, version_compare("0.12.1", "0.2"));
    $this->assertEquals(1, version_compare("0.13", "0.12.1"));
    $this->assertEquals(-1, version_compare("0.12.1", "0.13"));
  }

}
