<?php

require_once(__DIR__ . "/../inc/simpletest/autorun.php");

require_once(__DIR__ . "/../inc/global.php");

/**
 * Tests related to the configuration of crypto.php.
 */
class CryptoTestsTest extends UnitTestCase {

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
		$this->assertIdentical(array(), array_diff(get_all_currencies(), get_all_fiat_currencies(), get_all_cryptocurrencies(), get_all_commodity_currencies()));
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

	/**
	 * Tests that for all summaries (such as 'summary_nzd_bitnz') that the script to
	 * generate the summary value actually exists.
	 *
	 * Prevents errors like "Unknown summary type summary_usd_crypto-trade".
	 *
	 * TODO Could extend it to actually test summary.php, right now it just checks
	 * that the script file exists.
	 */
	function testAllSummaryCurrenciesHaveFiles() {
		foreach (get_summary_types() as $key => $data) {
			$file = __DIR__ . "/../jobs/summary/all2" . $data['key'] . ".php";
			// TODO make this to be for not just fiat currencies but for ALL currencies
			if (in_array($data['currency'], get_all_fiat_currencies())) {
				$this->assertTrue(file_exists($file), "Expected file '$file' to exist for summary '$key'");
			} else {
				// this will fail if we eventually create an all2btc job for example
				$this->assertFalse(file_exists($file), "Did not expect file '$file' to exist for summary '$key'");
			}
		}
	}

	/**
	 * All cryptocurrencies should have a 'totalX.php' file and 'crypto2X.php' file
	 * (This doesn't actually test that the jobs are implemented in summary.php)
	 */
	function testAllCryptoCurrenciesHaveFiles() {
		foreach (get_all_cryptocurrencies() as $cur) {
			$file = __DIR__ . "/../jobs/summary/total" . $cur . ".php";
			$this->assertTrue(file_exists($file), "Expected file '$file' to exist for cryptocurrency '$cur'");

			$file = __DIR__ . "/../jobs/summary/crypto2" . $cur . ".php";
			$this->assertTrue(file_exists($file), "Expected file '$file' to exist for cryptocurrency '$cur'");
		}
	}

	/**
	 * All hashrate cryptocurrencies should have a 'totalhashrate_X.php' file
	 * (This doesn't actually test that the jobs are implemented in summary.php)
	 */
	function testAllHashrateCurrenciesHaveFiles() {
		foreach (get_all_hashrate_currencies() as $cur) {
			$file = __DIR__ . "/../jobs/summary/totalhashrate_" . $cur . ".php";
			$this->assertTrue(file_exists($file), "Expected file '$file' to exist for hashrate currency '$cur'");
		}
	}

}