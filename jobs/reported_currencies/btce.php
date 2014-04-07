<?php

/**
 * BTC-e reported currencies job (#121).
 * BTC-e does not actually provide an API for supported coins, so we use an anonmyous user
 * to get pairs through the trade API.
 */

$account = array(
	'api_key' => get_site_config('btce_example_api_key'),
	'api_secret' => get_site_config('btce_example_api_secret'),
);

require(__DIR__ . "/../_btce.php");

$btce_info = btce_query($account['api_key'], $account['api_secret'], "getInfo");
if (isset($btce_info['error'])) {
	throw new ExternalAPIException("API returned error: '" . $btce_info['error'] . "'");
}

crypto_log("Found reported currencies " . print_r($btce_info['return']['funds'], true));

// update the database
$q = db()->prepare("DELETE FROM reported_currencies WHERE exchange=?");
$q->execute(array($exchange['name']));

foreach ($btce_info['return']['funds'] as $currency => $value) {
	$q = db()->prepare("INSERT INTO reported_currencies SET exchange=?, currency=?");
	$q->execute(array($exchange['name'], $currency));
}
