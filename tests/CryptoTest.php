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

	/**
	 * If we have disabled an account, it should not appear in {@link get_external_apis()}.
	 */
	function testDisabledAccountsArentExternalAPIs() {
		$account_data_grouped = account_data_grouped();
		$external_apis = get_external_apis();
		$this->assertGreaterThan(0, count($account_data_grouped['Mining pools']));
		foreach ($account_data_grouped['Mining pools'] as $key => $data) {
			if ($data['disabled']) {
				$this->assertFalse(isset($external_apis['Mining pool wallets'][$key]), "Did not expect disabled mining pool wallet '$key' in external APIs");
			}
		}
		$this->assertGreaterThan(0, count($account_data_grouped['Exchanges']));
		foreach ($account_data_grouped['Exchanges'] as $key => $data) {
			if ($data['disabled']) {
				$this->assertFalse(isset($external_apis['Exchange wallets'][$key]), "Did not expect disabled exchange wallet '$key' in external APIs");
			}
		}
		$this->assertGreaterThan(0, count($account_data_grouped['Securities']));
		foreach ($account_data_grouped['Securities'] as $key => $data) {
			if ($data['disabled']) {
				$this->assertFalse(isset($external_apis['Security exchanges'][$key]), "Did not expect disabled mining pool wallet '$key' in external APIs");
			}
		}
	}

	/**
	 * If we have disabled an account, it should not appear in {@link get_supported_wallets()}.
	 */
	function testDisabledAccountsArentWallets() {
		$account_data_grouped = account_data_grouped();
		$wallets = get_supported_wallets();
		$this->assertGreaterThan(0, count($account_data_grouped['Mining pools']));
		foreach ($account_data_grouped['Mining pools'] as $key => $data) {
			if ($data['disabled']) {
				$this->assertFalse(isset($wallets[$key]), "Did not expect disabled mining pool wallet '$key' in supported wallets");
			}
		}
		$this->assertGreaterThan(0, count($account_data_grouped['Exchanges']));
		foreach ($account_data_grouped['Exchanges'] as $key => $data) {
			if ($data['disabled']) {
				$this->assertFalse(isset($wallets[$key]), "Did not expect disabled exchange wallet '$key' in supported wallets");
			}
		}
		$this->assertGreaterThan(0, count($account_data_grouped['Securities']));
		foreach ($account_data_grouped['Securities'] as $key => $data) {
			if ($data['disabled']) {
				$this->assertFalse(isset($wallets[$key]), "Did not expect disabled mining pool wallet '$key' in supported wallets");
			}
		}
	}

	/**
	 * All exchanges, even those that are disabled, should have a definition through
	 * {@link #get_accounts_wizard_config_basic()}.
	 */
	function testAllAccountsHaveWizardConfig() {
		$account_data_grouped = account_data_grouped();
		foreach (array('Mining pools', 'Exchanges', 'Securities') as $group_key) {
			foreach ($account_data_grouped[$group_key] as $key => $data) {
				$this->assertNotNull(get_accounts_wizard_config_basic($key), "Expected a wizard config for exchange '$key'");
			}
		}

	}

  /**
   * All currencies defined in {@link get_address_currencies()} should have
   * an equivalent PHP script in {@code jobs/addresses/CUR.php}.
   */
  function testAllAddressCurrenciesHaveAddressIncludes() {
    foreach (get_address_currencies() as $cur) {
      // skip ones that are discovered
      if (\DiscoveredComponents\Currencies::hasKey($cur)) {
        continue;
      }

      $file = __DIR__ . "/../jobs/addresses/$cur.php";
      $this->assertTrue(file_exists($file), "File '$file' did not exist for address currency '$cur'");
    }
  }

}
