<?php

require_once(__DIR__ . "/../vendor/lastcraft/simpletest/autorun.php");

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/../layout/graphs.php");
require_once(__DIR__ . "/../graphs/types.php");

/**
 * Tests related to graphs.
 */
class GraphTestsTest extends UnitTestCase {

	function testDefaultExchangeTest() {
		$this->assertEqual('BTC-e for LTC/FTC, Bitstamp for USD, CEX.io for GHS', get_default_exchange_text(array('ltc', 'ftc', 'usd', 'ghs')));
		$this->assertEqual('BTC-e for LTC, Bitstamp for USD, CEX.io for GHS', get_default_exchange_text(array('ltc', 'usd', 'ghs')));
		$this->assertEqual('BTC-e for LTC/FTC', get_default_exchange_text(array('ltc', 'ftc')));
		$this->assertEqual('', get_default_exchange_text(array()));
	}

	/**
	 * Can probably remove this test when graphs are subclasses rather than
	 * associative arrays.
	 */
	function testAllGraphsHaveHeadings() {
		$graphs = graph_types();
		foreach ($graphs as $key => $type) {
			if (isset($type['category']) && $type['category']) {
				$this->assertFalse(isset($type['heading']), "Graph $key is a category and should not have a heading");
			} else if (isset($type['subcategory']) && $type['subcategory']) {
				$this->assertFalse(isset($type['heading']), "Graph $key is a subcategory and should not have a heading");
			} else {
				$this->assertTrue(isset($type['heading']), "Graph $key has no heading");
				$this->assertTrue($type['heading'], "Graph $key has an empty heading");
			}
		}
	}

}
