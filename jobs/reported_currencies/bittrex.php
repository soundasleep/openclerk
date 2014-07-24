<?php

/**
 * Bittrex reported currencies job (#171).
 */

$exchange_name = "bittrex";

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://bittrex.com/api/v1.1/public/getmarketsummaries")));
if (!isset($rates['success']) || !$rates['success']) {
	if (isset($rates['message'])) {
		throw new ExternalAPIException($rates['message']);
	} else {
		throw new ExternalAPIException("API failed with no message");
	}
}

$currencies = array();
foreach ($rates['result'] as $rate) {
	$pair = explode("-", $rate['MarketName']);
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
