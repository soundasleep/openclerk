<?php

/**
 * ANXPRO ticker job.
 */

$exchange_name = "anxpro";
$get_exchange_pairs = get_exchange_pairs();
$first = true;
foreach ($get_exchange_pairs['anxpro'] as $pair) {
	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_anxpro_ticker') * 2));
		sleep(get_site_config('sleep_anxpro_ticker'));
	}
	$first = false;

	$currency1 = $pair[0];
	$currency2 = $pair[1];

	// map our internal currency order to the order documented on ANXPRO API docs
	// it looks like this isn't even necessary
	if (is_fiat_currency($currency1) || $currency1 == 'btc') {
		$key = get_currency_abbr($currency2) . get_currency_abbr($currency1);
	} else {
		$key = get_currency_abbr($currency1) . get_currency_abbr($currency2);
	}

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://anxpro.com/api/2/" . $key . "/money/ticker")));

	if (!isset($rates['data']['last'])) {
		throw new ExternalAPIException("No $currency1/$currency2 ($key) last rate for $exchange_name");
	}

	if (!$rates['data']['buy']['value'] && !$rates['data']['sell']['value']) {
		crypto_log("Ignoring pair $key: bid/ask is zero");
		continue;
	}

	insert_new_ticker($job, $exchange, $currency1, $currency2, array(
		"last_trade" => $rates['data']['last']['value'],		// we could use value_int too
		// it seems ANX gets these swapped around
		"bid" => $rates['data']['buy']['value'],
		"ask" => $rates['data']['sell']['value'],
		"volume" => $rates['data']['vol']['value'],
		// also 'high', 'low', 'avg', 'vwap'
	));

}
