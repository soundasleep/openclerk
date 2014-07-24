<?php

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/OpenclerkTest.php");

/**
 * Issue #252: Generate and update README.md automatically
 */
class GenerateReadmeTest extends OpenclerkTest {

	function testGenerate() {
		$templates = array(
			'currencies_list' => array(),
			'currencies_inline' => array(),
			'fiat_currencies_list' => array(),
			'fiat_currencies_inline' => array(),
			'crypto_currencies_list' => array(),
			'crypto_currencies_inline' => array(),
			'commodity_currencies_list' => array(),
			'commodity_currencies_inline' => array(),
			'exchange_wallets_list' => array(),
			'mining_pools_list' => array(),
			'securities_list' => array(),
			'exchange_list' => array(),
		);

		foreach (get_all_currencies() as $cur) {
			$templates['currencies_list'][] = "  * " . get_currency_name($cur);
			$templates['currencies_inline'][] = get_currency_abbr($cur);
		}
		foreach (get_all_fiat_currencies() as $cur) {
			$templates['fiat_currencies_list'][] = "  * " . get_currency_name($cur);
			$templates['fiat_currencies_inline'][] = get_currency_abbr($cur);
		}
		foreach (get_all_cryptocurrencies() as $cur) {
			$templates['crypto_currencies_list'][] = "  * " . get_currency_name($cur);
			$templates['crypto_currencies_inline'][] = get_currency_abbr($cur);
		}
		foreach (get_all_commodity_currencies() as $cur) {
			$templates['commodity_currencies_list'][] = "  * " . get_currency_name($cur);
			$templates['commodity_currencies_inline'][] = get_currency_abbr($cur);
		}
		$grouped = account_data_grouped();
		foreach ($grouped['Exchanges'] as $key => $data) {
			if (!$data['disabled']) {
				$templates['exchange_wallets_list'][] = "  * " . get_exchange_name($key);
			}
		}
		foreach ($grouped['Mining pools'] as $key => $data) {
			if (!$data['disabled']) {
				$templates['mining_pools_list'][] = "  * " . get_exchange_name($key);
			}
		}
		foreach ($grouped['Securities'] as $key => $data) {
			if (!$data['disabled']) {
				$templates['securities_list'][] = "  * " . get_exchange_name($key);
			}
		}
		foreach (get_exchange_pairs() as $key => $pairs) {
			$templates['exchange_list'][] = "  * " . get_exchange_name($key);
		}

		$templates['currencies_list'] = implode("\n", array_unique($templates['currencies_list']));
		$templates['fiat_currencies_list'] = implode("\n", array_unique($templates['fiat_currencies_list']));
		$templates['crypto_currencies_list'] = implode("\n", array_unique($templates['crypto_currencies_list']));
		$templates['commodity_currencies_list'] = implode("\n", array_unique($templates['commodity_currencies_list']));
		$templates['exchange_wallets_list'] = implode("\n", array_unique($templates['exchange_wallets_list']));
		$templates['mining_pools_list'] = implode("\n", array_unique($templates['mining_pools_list']));
		$templates['securities_list'] = implode("\n", array_unique($templates['securities_list']));
		$templates['exchange_list'] = implode("\n", array_unique($templates['exchange_list']));

		$templates['currencies_inline'] = implode(", ", array_unique($templates['currencies_inline']));
		$templates['fiat_currencies_inline'] = implode(", ", array_unique($templates['fiat_currencies_inline']));
		$templates['crypto_currencies_inline'] = implode(", ", array_unique($templates['crypto_currencies_inline']));
		$templates['commodity_currencies_inline'] = implode(", ", array_unique($templates['commodity_currencies_inline']));

		// load the template
		$input = file_get_contents(__DIR__ . "/../README.template.md");
		foreach ($templates as $key => $value) {
			$input = str_replace('{$' . $key . '}', $value, $input);
		}

		// write it out
		file_put_contents(__DIR__ . "/../README.md", $input);

	}

}
