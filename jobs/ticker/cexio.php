<?php

/**
 * CEX.io ticker job.
 */

$exchange_name = "cexio";
$get_exchange_pairs = get_exchange_pairs();
$first = true;
foreach ($get_exchange_pairs['cexio'] as $pair) {
	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_cexio_ticker') * 2));
		sleep(get_site_config('sleep_cexio_ticker'));
	}
	$first = false;

	$currency1 = $pair[0];
	$currency2 = $pair[1];

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://cex.io/api/ticker/" . get_currency_abbr($currency2) . "/" . get_currency_abbr($currency1))));

	if (!isset($rates['last'])) {
		throw new ExternalAPIException("No $currency1/$currency2 last rate for $exchange_name");
	}

	insert_new_ticker($job, $exchange, $currency1, $currency2, array(
		"last_trade" => $rates['last'],
		"bid" => $rates['bid'],
		"ask" => $rates['ask'],
		"volume" => $rates['volume'], // last 24h
		// ignores 'high' (last 24h), 'low' (last 24h)
	));

}
