<?php

/**
 * Bitcurex ticker job (both PLN and EUR).
 */

{
	$exchange_name = "bitcurex";
	$currency1 = "pln";
	$currency2 = "btc";

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://pln.bitcurex.com/data/ticker.json")));

	if (!isset($rates['last'])) {
		throw new ExternalAPIException("No $currency1/$currency2 last rate for $exchange_name");
	}

	crypto_log("$exchange_name rate for $currency1/$currency2: " . $rates['last']);

	insert_new_ticker($job, $exchange, $currency1, $currency2, array(
		// also high, low, avg, vwap, time
		"last_trade" => $rates['last'],
		"bid" => $rates['buy'],
		"ask" => $rates['sell'],
		"volume" => $rates['vol'],
	));
}

{
	$exchange_name = "bitcurex";
	$currency1 = "eur";
	$currency2 = "btc";

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://eur.bitcurex.com/data/ticker.json")));

	if (!isset($rates['last'])) {
		throw new ExternalAPIException("No $currency1/$currency2 last rate for $exchange_name");
	}

	crypto_log("$exchange_name rate for $currency1/$currency2: " . $rates['last']);

	insert_new_ticker($job, $exchange, $currency1, $currency2, array(
		// also high, low, avg, vwap, time
		"last_trade" => $rates['last'],
		"bid" => $rates['buy'],
		"ask" => $rates['sell'],
		"volume" => $rates['vol'],
	));
}
