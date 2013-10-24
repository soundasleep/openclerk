<?php

require_once(__DIR__ . "/../inc/simpletest/autorun.php");

require_once(__DIR__ . "/../inc/global.php");

/**
 * Tests related to the configuration of crypto.php.
 */
class CryptoTestsTest extends UnitTestCase {

	function testGetAllFiatCurrencies() {
		$fiat = get_all_fiat_currencies();
		$this->assertTrue(array_search('usd', $fiat));
		$this->assertFalse(array_search('btc', $fiat));
		$this->assertFalse(array_search('ghs', $fiat));
	}

}