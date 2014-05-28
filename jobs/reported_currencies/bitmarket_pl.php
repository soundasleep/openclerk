<?php

/**
 * BitMarket.pl reported currencies job (#202).
 * BitMarket.pl does not actually provide an API for supported coins, so we use an anonmyous user
 * to get pairs through the trade API.
 */

$account = array(
	'api_key' => get_site_config('bitmarket_pl_example_api_key'),
	'api_secret' => get_site_config('bitmarket_pl_example_api_secret'),
);

require(__DIR__ . "/../_bitmarket_pl.php");

$info = bitmarket_pl_query($account['api_key'], $account['api_secret'], "info");

if (isset($info['error'])) {
	if (isset($info['errorMsg'])) {
		throw new ExternalAPIException("API returned error: '" . htmlspecialchars($info['errorMsg']) . "'");
	} else {
		throw new ExternalAPIException("API returned error " . htmlspecialchars($info['error']) . "");
	}
}

$currencies = array_keys($info['data']['balances']['available']);
crypto_log("Found reported currencies " . print_r($currencies, true));

// update the database
$q = db()->prepare("DELETE FROM reported_currencies WHERE exchange=?");
$q->execute(array($exchange['name']));

foreach ($currencies as $currency) {
	$q = db()->prepare("INSERT INTO reported_currencies SET exchange=?, currency=?");
	$q->execute(array($exchange['name'], $currency));
}
