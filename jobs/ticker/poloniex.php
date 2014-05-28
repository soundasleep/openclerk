<?php

/**
 * Poloniex ticker job.
 */

$exchange_name = "poloniex";
$exchange_pairs = get_exchange_pairs();
$currencies = $exchange_pairs['poloniex'];

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://poloniex.com/public?command=returnTicker")));

foreach ($currencies as $pair) {
	$key = get_currency_abbr($pair[0]) . "_" . get_currency_abbr($pair[1]);
	if (!isset($rates[$key])) {
		throw new ExternalAPIException("Pair $key was not found");
	}

	insert_new_ticker($job, $exchange, $pair[0], $pair[1], array(
		"last_trade" => $rates[$key]['last'],
		"bid" => $rates[$key]['highestBid'],
		"ask" => $rates[$key]['lowestAsk'],
		"volume" => $rates[$key]['baseVolume'], // last 24h
		// ignores 'percentChange', 'quoteVolume', 'isFrozen'
	));
}
