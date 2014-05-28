<?php

/**
 * Mintpal reported currencies job.
 */

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://api.mintpal.com/v1/market/summary/")));

$currencies = array();
foreach ($rates as $rate) {
	$currencies[] = strtolower($rate['code']);
	$currencies[] = strtolower($rate['exchange']);
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
