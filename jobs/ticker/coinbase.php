<?php

/**
 * Coinbase ticker job.
 */

// no bid,ask prices; just single prices

$exchange_name = "coinbase";
$exchange_pairs = get_exchange_pairs();
$currencies = $exchange_pairs['coinbase'];

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://coinbase.com/api/v1/currencies/exchange_rates")));

foreach ($currencies as $pair) {
	$key = $pair[1] . "_to_" . $pair[0];		// more precision than 1/nzd_to_btc
	if (!isset($rates[$key])) {
		throw new ExternalAPIException("Pair $key was not found");
	}

	insert_new_ticker($job, $exchange, $pair[0], $pair[1], array(
		"last_trade" => $rates[$key],
		// no bid
		// no ask
		// no volume
	));
}
