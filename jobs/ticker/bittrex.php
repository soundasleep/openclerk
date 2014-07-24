<?php

/**
 * Bittrex ticker job.
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

$exchange_pairs = get_exchange_pairs();
foreach ($exchange_pairs['bittrex'] as $pair) {
	$found = false;
	foreach ($rates['result'] as $rate) {
		$key = get_currency_abbr($pair[0]) . "-" . get_currency_abbr($pair[1]);
		if ($rate['MarketName'] == $key) {
			insert_new_ticker($job, $exchange, $pair[0], $pair[1], array(
				// also High, Low, BaseVolume, OpenBuyOrders, OpenSellOrders, Prevday
				"last_trade" => $rate['Last'],
				"bid" => $rate['Bid'],
				"ask" => $rate['Ask'],
				"volume" => $rate['Volume'],
			));
			$found = true;
			break;
		}
	}
	if (!$found) {
		throw new ExternalAPIException("Found no " . $exchange_name . " rate for currency pair $key");
	}
}
