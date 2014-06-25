<?php

/**
 * itBit ticker job.
 */

$exchange_name = "itbit";
$get_exchange_pairs = get_exchange_pairs();
$first = true;
foreach ($get_exchange_pairs['itbit'] as $pair) {
	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_itbit_ticker') * 2));
		sleep(get_site_config('sleep_itbit_ticker'));
	}
	$first = false;

	$currency1 = $pair[0];
	$currency2 = $pair[1];

	// 'btc' becomes 'xbt'
	$key = get_currency_abbr($currency2 == "btc" ? "xbt" : $currency2) . get_currency_abbr($currency1);

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.itbit.com/api/v2/markets/" . $key . "/orders")));

	if (isset($rates['message'])) {
		throw new ExternalAPIException($rates['message']);
	}
	if (!isset($rates['bids']) || count($rates['bids']) == 0) {
		throw new ExternalAPIException("No bids for $currency1/$currency2 ($key) in $exchange_name");
	}
	if (!isset($rates['asks']) || count($rates['asks']) == 0) {
		throw new ExternalAPIException("No asks for $currency1/$currency2 ($key) in $exchange_name");
	}

	// and also get last_trade
	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_itbit_ticker') * 2));
		sleep(get_site_config('sleep_itbit_ticker'));
	}
	$first = false;

	// TODO keep track of last trade ID and use that as ?since parameter
	$trades = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.itbit.com/api/v2/markets/" . $key . "/trades?since=0")));

	if (!isset($trades[0])) {
		throw new ExternalAPIException("No last trade for $currency1/$currency2 ($key) in $exchange_name");
	}

	insert_new_ticker($job, $exchange, $currency1, $currency2, array(
		"last_trade" => $trades[0]['price'],
		// it seems ANX gets these swapped around
		"bid" => $rates['bids'][0][0],
		"ask" => $rates['asks'][0][0],
		// no volume
	));

}
