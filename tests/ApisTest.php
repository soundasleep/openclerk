<?php

use \Openclerk\Config;

require_once(__DIR__ . "/../inc/global.php");

/**
 * Test that APIs are available.
 */
class ApisTest extends PHPUnit_Framework_TestCase {

  function getJSON($path) {
    $uri = Config::get('absolute_url') . $path;
    $raw = file_get_contents($uri);
    $json = json_decode($raw, true /* assoc */);
    if (!$json) {
      throw new Exception("'$uri' did not return valid JSON: '$raw'");
    }
    return $json;
  }

  function testRates() {
    $json = $this->getJSON("api/v1/rates.json");
    $this->assertTrue($json['success']);
    $this->assertTrue(is_array($json['result']));
    $this->assertTrue(isset($json['time']));
  }

}
