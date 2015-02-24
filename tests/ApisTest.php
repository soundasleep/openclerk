<?php

use \Openclerk\Config;

require_once(__DIR__ . "/../inc/global.php");

class ApisTestException extends \Exception { }

/**
 * Test that APIs are available.
 */
class ApisTest extends PHPUnit_Framework_TestCase {

  function getJSON($path) {
    $uri = Config::get('absolute_url') . $path;
    $raw = @file_get_contents($uri);
    $json = json_decode($raw, true /* assoc */);
    if (!$json) {
      throw new ApisTestException("'$uri' did not return valid JSON: '$raw'");
    }
    return $json;
  }

  function testRates() {
    $json = $this->getJSON("api/v1/rates.json");
    $this->assertTrue($json['success']);
    $this->assertTrue(is_array($json['result']));
    $this->assertTrue(isset($json['time']));
  }

  function testRatesWithoutJson() {
    $json = $this->getJSON("api/v1/rates");
    $this->assertTrue($json['success']);
    $this->assertTrue(is_array($json['result']));
    $this->assertTrue(isset($json['time']));
  }

  function testGraphsStatisticsQueue() {
    $json = $this->getJSON("api/v1/graphs/statistics_queue.json");
    $this->assertTrue($json['success']);
    $this->assertTrue(is_array($json['result']));
    $this->assertTrue(isset($json['time']));
    $this->assertTrue(is_array($json['result']['columns']));
  }

  function testGraphsStatisticsQueueWithoutJson() {
    $json = $this->getJSON("api/v1/graphs/statistics_queue");
    $this->assertTrue($json['success']);
    $this->assertTrue(is_array($json['result']));
    $this->assertTrue(isset($json['time']));
    $this->assertTrue(is_array($json['result']['columns']));
  }

  function testFailing() {
    try {
      $json = $this->getJSON("api/v1/404");
      $this->fail("Should not have been able to get missing API");
    } catch (ApisTestException $e) {
      // expected
    }
  }

}
