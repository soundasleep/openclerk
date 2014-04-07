<?php

/**
 * Coins-e reported currencies job (#121).
 */

$coins = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.coins-e.com/api/v2/coins/list/")));

$currencies = array();
foreach ($coins['coins'] as $coin) {
	$currencies[] = strtolower($coin['coin']);
	// also available: 'status': 'healthy', 'maintenance'
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
