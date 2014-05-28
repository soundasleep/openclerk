<?php

/**
 * Poloniex reported currencies job.
 */

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://poloniex.com/public?command=returnTicker")));

$currencies = array();
foreach ($rates as $key => $ignored) {
	$bits = explode("_", $key, 2);
	if (count($bits) == 2) {
		$currencies[] = strtolower($bits[0]);
		$currencies[] = strtolower($bits[1]);
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
