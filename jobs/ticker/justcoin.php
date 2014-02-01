<?php

/**
 * Justcoin ticker job.
 */

$exchange_name = "justcoin";
$currency1 = "pln";
$currency2 = "btc";

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://justcoin.com/api/v1/markets")));

$exchange_pairs = get_exchange_pairs();
foreach ($exchange_pairs['justcoin'] as $pair) {
	$found = false;
	foreach ($rates as $rate) {
		// pairs = (usd/btc) or (btc/ltc)
		if ($rate['id'] == get_currency_abbr($pair[0]) . get_currency_abbr($pair[1])) {
			// BTC/LTC: crypto/crypto
			// we need to flip these values over
			// I don't know what the 'scale' parameter means
			insert_new_ticker($job, $exchange, $pair[0], $pair[1], array(
				// also scale, high, low
				"last_trade" => ($rate['last'] == 0) ? 0 : (1 / $rate['last']),
				"ask" => ($rate['bid'] == 0) ? 0 : (1 / $rate['bid']),
				"bid" => ($rate['ask'] == 0) ? 0 : (1 / $rate['ask']),
				"volume" => $rate['volume'],
			));
			$found = true;
			break;
		} else if ($rate['id'] == get_currency_abbr($pair[1]) . get_currency_abbr($pair[0])) {
			// USD/BTC: fiat/crypto
			// I don't know what the 'scale' parameter means
			insert_new_ticker($job, $exchange, $pair[0], $pair[1], array(
				// also scale, high, low
				"last_trade" => $rate['last'],
				"bid" => $rate['bid'],
				"ask" => $rate['ask'],
				"volume" => $rate['volume'],
			));
			$found = true;
			break;
		}
	}
	if (!$found) {
		throw new ExternalAPIException("Found no " . $exchange_name . " rate for currency pair " . implode('/', $pair));
	}
}
