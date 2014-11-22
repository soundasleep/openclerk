<?php

/**
 * Bter reported currencies job.
 */

$exchange_name = "bter";

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://data.bter.com/api/1/pairs")));

$currencies = array();
foreach ($rates as $rate) {
	$pair = explode("_", $rate);
	if (count($pair) == 2) {
		$currencies[] = strtolower($pair[0]);
		$currencies[] = strtolower($pair[1]);
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
