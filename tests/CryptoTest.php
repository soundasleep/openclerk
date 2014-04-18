<?php

require_once(__DIR__ . "/../inc/global.php");

/**
 * Tests related to the configuration of crypto.php.
 */
class CryptoTestsTest extends PHPUnit_Framework_TestCase {

	function testGetAllFiatCurrencies() {
		$fiat = get_all_fiat_currencies();
		$this->assertTrue(in_array('usd', $fiat));
		$this->assertFalse(in_array('btc', $fiat));
		$this->assertFalse(in_array('ghs', $fiat));
	}

	function testAllCurrenciesPresent() {
		foreach (get_all_fiat_currencies() as $c) {
			$this->assertTrue(in_array($c, get_all_currencies()));
		}
		foreach (get_all_cryptocurrencies() as $c) {
			$this->assertTrue(in_array($c, get_all_currencies()));
		}
		foreach (get_all_commodity_currencies() as $c) {
			$this->assertTrue(in_array($c, get_all_currencies()));
		}
	}

	function testAllCurrenciesComplete() {
		$this->assertSame(array(), array_diff(get_all_currencies(), get_all_fiat_currencies(), get_all_cryptocurrencies(), get_all_commodity_currencies()));
	}

	function testAllWalletCurrencies() {
		foreach (get_supported_wallets() as $exchange => $currencies) {
			foreach ($currencies as $c) {
				if ($c == 'hash') continue;
				$this->assertTrue(in_array($c, get_all_currencies()), "Exchange $exchange had invalid currency $c");
			}
		}
	}

	function testAllExchangeCurrencies() {
		foreach (get_exchange_pairs() as $exchange => $pairs) {
			foreach ($pairs as $p) {
				$this->assertTrue(in_array($p[0], get_all_currencies()), "Exchange $exchange had invalid currency $p[0] in pair $p[0]/$p[1]");
				$this->assertTrue(in_array($p[1], get_all_currencies()), "Exchange $exchange had invalid currency $p[1] in pair $p[0]/$p[1]");
			}
		}
	}

}
