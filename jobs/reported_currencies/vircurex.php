<?php

/**
 * Vircurex reported currencies job (#121).
 */

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://api.vircurex.com/api/get_info_for_currency.json")));

$currencies = array();
foreach ($rates as $currency1 => $supported) {
	if ($currency1 == "status")
		continue;

	$currencies[] = strtolower($currency1);
	foreach ($supported as $currency2 => $data) {
		$currencies[] = strtolower($currency2);
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
