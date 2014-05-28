<?php

/**
 * BitMarket.pl ticker job.
 */

$exchange_name = "bitmarket_pl";
$get_exchange_pairs = get_exchange_pairs();
$first = true;
foreach ($get_exchange_pairs['bitmarket_pl'] as $pair) {
	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_bitmarket_pl_ticker') * 2));
		sleep(get_site_config('sleep_bitmarket_pl_ticker'));
	}
	$first = false;

	$currency1 = $pair[0];
	$currency2 = $pair[1];

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.bitmarket.pl/json/" . get_currency_abbr($currency2) . get_currency_abbr($currency1) . "/ticker.json")));

	if (!isset($rates['last'])) {
		throw new ExternalAPIException("No $currency1/$currency2 last rate for $exchange_name");
	}

	insert_new_ticker($job, $exchange, $currency1, $currency2, array(
		"last_trade" => $rates['last'],
		"bid" => $rates['bid'],
		"ask" => $rates['ask'],
		"volume" => $rates['volume'], // last 24h
		// ignores 'high' (last 24h), 'low' (last 24h), 'vwap'
	));

}
