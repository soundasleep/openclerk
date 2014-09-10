<?php

/**
 * Crypto-Trade reported currencies job (#121).
 * We will use the public /tickers API to get all reported currencies.
 */

$coins = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://crypto-trade.com/api/1/tickers")));

$currencies = array();
foreach ($coins['data'] as $data) {
	foreach ($data as $key => $values) {
		$bits = explode("_", $key);
		$currencies[] = $bits[0];
		$currencies[] = $bits[1];
	}
}

$currencies = array_unique($currencies);
crypto_log("Found currencies " . implode(", ", $currencies));

// update the database
$q = db()->prepare("DELETE FROM reported_currencies WHERE exchange=?");
$q->execute(array($exchange['name']));

foreach ($currencies as $cur) {
	$q = db()->prepare("INSERT INTO reported_currencies SET exchange=?, currency=?");
	$q->execute(array($exchange['name'], $cur));
}
