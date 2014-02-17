<?php

/**
 * Vault of Satoshi ticker job.
 * Vault of Satoshi only trades (fiat, crypto) pairs - no (crypto, crypto) or (fiat, fiat)
 */

$get_exchange_pairs = get_exchange_pairs();
$pairs = $get_exchange_pairs['vaultofsatoshi']; // (usd, btc), ...
$exchange_name = "vaultofsatoshi";

$first = true;
foreach ($pairs as $pair) {
	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_vaultofsatoshi_ticker') * 2));
		sleep(get_site_config('sleep_vaultofsatoshi_ticker'));
	}
	$first = false;

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://api.vaultofsatoshi.com/public/ticker?order_currency=" . get_currency_abbr($pair[1]) . "&payment_currency=" . get_currency_abbr($pair[0]))));

	if (isset($rates['message']) && $rates['message']) {
		crypto_log(print_r($rates, true));
		throw new ExternalAPIException("Could not find " . $pair[0] . "/" . $pair[1] . " rate for $exchange_name: " . htmlspecialchars($rates['message']));
	}

	if (!isset($rates['data']['closing_price']['value'])) {
		throw new ExternalAPIException("Could not find " . $pair[0] . "/" . $pair[1] . " closing price for $exchange_name.");
	}

	$last_trade = $rates['data']['closing_price']['value'];	// last 24h
	$volume = $rates['data']['volume_1day']['value'];	// last 24h

	// getting buy/ask will require orderbook
	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_vaultofsatoshi_ticker') * 2));
		sleep(get_site_config('sleep_vaultofsatoshi_ticker'));
	}
	$first = false;

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://api.vaultofsatoshi.com/public/orderbook?order_currency=" . get_currency_abbr($pair[1]) . "&payment_currency=" . get_currency_abbr($pair[0]) . "&count=1")));

	if (isset($rates['message']) && $rates['message']) {
		crypto_log(print_r($rates, true));
		throw new ExternalAPIException("Could not find " . $pair[0] . "/" . $pair[1] . " rate for $exchange_name: " . htmlspecialchars($rates['message']));
	}

	if (!isset($rates['data']['bids'][0]['price']['value'])) {
		throw new ExternalAPIException("Could not find " . $pair[0] . "/" . $pair[1] . " bid for $exchange_name.");
	}
	if (!isset($rates['data']['asks'][0]['price']['value'])) {
		throw new ExternalAPIException("Could not find " . $pair[0] . "/" . $pair[1] . " ask for $exchange_name.");
	}

	insert_new_ticker($job, $exchange, $pair[0], $pair[1], array(
		"last_trade" => $last_trade,
		"bid" => $rates['data']['bids'][0]['price']['value'],
		"ask" => $rates['data']['asks'][0]['price']['value'],
		"volume" => $volume,
	));

}
