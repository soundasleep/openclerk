<?php

/**
 * Bter ticker job.
 */

$exchange_name = "bter";

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://data.bter.com/api/1/tickers")));

$exchange_pairs = get_exchange_pairs();
foreach ($exchange_pairs['bter'] as $pair) {
	$key = strtolower(get_currency_abbr($pair[1])) . "_" . strtolower(get_currency_abbr($pair[0]));
	if (isset($rates[$key])) {
		$rate = $rates[$key];
		insert_new_ticker($job, $exchange, $pair[0], $pair[1], array(
			// also high, low
			"last_trade" => $rate['last'],
			"bid" => $rate['buy'],
			"ask" => $rate['sell'],
			"volume" => $rate['vol_' . strtolower(get_currency_abbr($pair[0]))],
		));
	} else {
		throw new ExternalAPIException("Found no $exchange_name rate for currency pair $key");
	}
}
