<?php

/**
 * Coinbase reported currencies job (#121).
 * Coinbase /api/currencies only reports fiat currencies, so we use /exchange_rates to find
 * both cryptocurrencies and fiat currencies
 */

$currencies = array();
$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://coinbase.com/api/v1/currencies/exchange_rates")));
foreach ($rates as $rate => $value) {
	$bits = explode("_to_", strtolower($rate));
	if (count($bits) == 2) {
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
