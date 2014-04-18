<?php

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/../layout/graphs.php");
require_once(__DIR__ . "/../graphs/types.php");

/**
 * Tests related to graphs.
 */
class GraphTestsTest extends PHPUnit_Framework_TestCase {

	function testDefaultExchangeTest() {
		$this->assertEquals('BTC-e for LTC/FTC, Bitstamp for USD, CEX.io for GHS', get_default_exchange_text(array('ltc', 'ftc', 'usd', 'ghs')));
		$this->assertEquals('BTC-e for LTC, Bitstamp for USD, CEX.io for GHS', get_default_exchange_text(array('ltc', 'usd', 'ghs')));
		$this->assertEquals('BTC-e for LTC/FTC', get_default_exchange_text(array('ltc', 'ftc')));
		$this->assertEquals('', get_default_exchange_text(array()));
	}

	/**
	 * Can probably remove this test when graphs are subclasses rather than
	 * associative arrays.
	 */
	function testAllGraphsHaveHeadings() {
		// we need to login because private graphs require summary currencies
		$_SESSION["user_id"] = 200;
		$_SESSION["user_name"] = "Testing user";
		$_SESSION["user_key"] = "testing-key";
		global $global_user_logged_in;
		$global_user_logged_in = true;

		$graphs = graph_types();
		foreach ($graphs as $key => $type) {
			if (isset($type['category']) && $type['category']) {
				$this->assertEmpty(isset($type['heading']), "Graph $key is a category and should not have a heading");
			} else if (isset($type['subcategory']) && $type['subcategory']) {
				$this->assertEmpty(isset($type['heading']), "Graph $key is a subcategory and should not have a heading");
			} else {
				$this->assertNotEmpty(isset($type['heading']), "Graph $key has no heading");
				$this->assertNotEmpty($type['heading'], "Graph $key has an empty heading");
			}
		}
	}

}
