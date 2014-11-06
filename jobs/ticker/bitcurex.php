<?php

/**
 * Bitcurex ticker job (both PLN and EUR).
 */

$exchange_name = "bitcurex";
$get_exchange_pairs = get_exchange_pairs();

foreach ($get_exchange_pairs['bitcurex'] as $pair) {
	$currency1 = $pair[0];
	$currency2 = $pair[1];

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://bitcurex.com/api/" . urlencode($currency1) . "/ticker.json")));

	if (!isset($rates['last_tx_price'])) {
		throw new ExternalAPIException("No $currency1/$currency2 last rate for $exchange_name");
	}

	insert_new_ticker($job, $exchange, $currency1, $currency2, array(
		"last_trade" => $rates['last_tx_price'] / 1e4,
		"bid" => $rates['best_bid'] / 1e4,
		"ask" => $rates['best_ask'] / 1e4,
		"volume" => $rates['total_volume'] / 1e8,
	));
}
