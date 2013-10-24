<?php

require_once(__DIR__ . "/../inc/simpletest/autorun.php");

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/../layout/graphs.php");

/**
 * Tests related to graphs.
 */
class GraphTestsTest extends UnitTestCase {

	function testDefaultExchangeTest() {
		$this->assertEqual('BTC-e for LTC/FTC, Mt.Gox for USD, CEX.io for GHS', get_default_exchange_text(array('ltc', 'ftc', 'usd', 'ghs')));
		$this->assertEqual('BTC-e for LTC, Mt.Gox for USD, CEX.io for GHS', get_default_exchange_text(array('ltc', 'usd', 'ghs')));
		$this->assertEqual('BTC-e for LTC/FTC', get_default_exchange_text(array('ltc', 'ftc')));
		$this->assertEqual('', get_default_exchange_text(array()));
	}

}