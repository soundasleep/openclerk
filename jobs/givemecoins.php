<?php

/**
 * Give Me Coins balance job.
 */

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['givemecoins'];

$first = true;
foreach ($currencies as $currency) {
	if ($currency == "hash") {
		continue;
	}

	$exchange = "givemecoins";
	$url = "https://give-me-coins.com/pool/api-" . strtolower(get_currency_abbr($currency)) . "?api_key=";
	$table = "accounts_givemecoins";

	require(__DIR__ . "/_mmcfe_pool.php");

	if (!$first && get_site_config('sleep_givemecoins')) {
		set_time_limit(30 + (get_site_config('sleep_givemecoins') * 2));
		sleep(get_site_config('sleep_givemecoins'));
	}
	$first = false;
}

// no way to API for NMC?
