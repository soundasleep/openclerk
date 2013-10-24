<?php

require_once(__DIR__ . "/../inc/simpletest/autorun.php");

require_once(__DIR__ . "/../inc/global.php");

/**
 * Tests related to the configuration of crypto.php.
 */
class CryptoTestsTest extends UnitTestCase {

	function testGetAllFiatCurrencies() {
		$fiat = get_all_fiat_currencies();
		$this->assertTrue(array_search('usd', $fiat) !== false);
		$this->assertFalse(array_search('btc', $fiat) !== false);
		$this->assertFalse(array_search('ghs', $fiat) !== false);
	}

	function testAllCurrenciesPresent() {
		foreach (get_all_fiat_currencies() as $c) {
			$this->assertTrue(array_search($c, get_all_currencies()) !== false);
		}
		foreach (get_all_cryptocurrencies() as $c) {
			$this->assertTrue(array_search($c, get_all_currencies()) !== false);
		}
		foreach (get_all_commodity_currencies() as $c) {
			$this->assertTrue(array_search($c, get_all_currencies()) !== false);
		}
	}

	function testAllCurrenciesComplete() {
		$this->assertIdentical(array(), array_diff(get_all_currencies(), get_all_fiat_currencies(), get_all_cryptocurrencies(), get_all_commodity_currencies()));
	}

}