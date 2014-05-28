<?php

/**
 * MuPool FTC pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "mupool";
$table = "accounts_mupool";

$wallets = get_supported_wallets();
$first = true;
foreach ($wallets["mupool"] as $currency) {
	// sleep between requests
	if (!$first) {
		set_time_limit(get_site_config('sleep_mupool_balance') * 2);
		sleep(get_site_config('sleep_mupool_balance'));
	}
	$first = false;

	if ($currency == 'hash') {
		continue;
	}

	$api_url = "https://mupool.com/index.php?page=api&coin=" . get_currency_abbr($currency) . "&";
	require(__DIR__ . "/_mpos_pool.php");
}
