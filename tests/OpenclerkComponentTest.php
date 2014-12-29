<?php

require_once(__DIR__ . "/../inc/global.php");

/**
 * Test the Openclerk compent itself.
 */
class OpenclerkComponentTest extends \ComponentTests\ComponentTest {

  function getRoots() {
    return array(__DIR__ . "/..");
  }

  /**
   * May be extended by child classes to define a list of path
   * names that will be excluded by {@link #iterateOver()}.
   */
  function getExcludes() {
    return array("/vendor/", "/node_modules/", "/.sass-cache/", "/.tmp/");
  }

}
