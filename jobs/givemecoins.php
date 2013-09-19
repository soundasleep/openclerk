<?php

/**
 * Give Me Coins balance job.
 */

{
	$exchange = "givemecoins";
	$url = "https://give-me-coins.com/pool/api-ltc?api_key=";
	$currency = 'ltc';
	$table = "accounts_givemecoins";

	require("_mmcfe_pool.php");
}

if (get_site_config('sleep_givemecoins')) {
	set_time_limit(30 + (get_site_config('sleep_givemecoins') * 2));
	sleep(get_site_config('sleep_givemecoins'));
}

{
	$exchange = "givemecoins";
	$url = "https://give-me-coins.com/pool/api-btc?api_key=";
	$currency = 'btc';
	$table = "accounts_givemecoins";

	require("_mmcfe_pool.php");
}

if (get_site_config('sleep_givemecoins')) {
	set_time_limit(30 + (get_site_config('sleep_givemecoins') * 2));
	sleep(get_site_config('sleep_givemecoins'));
}

{
	$exchange = "givemecoins";
	$url = "https://give-me-coins.com/pool/api-ftc?api_key=";
	$currency = 'ftc';
	$table = "accounts_givemecoins";

	require("_mmcfe_pool.php");
}

// no way to API for NMC?
