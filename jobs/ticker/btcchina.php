<?php

/**
 * BTC China ticker job.
 */

$exchange = "btcchina";
$currency1 = "cny";
$currency2 = "btc";

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://data.btcchina.com/data/ticker")));

if (!isset($rates['ticker']['last'])) {
	throw new ExternalAPIException("No $currency1/$currency2 last rate for $exchange");
}

crypto_log("$exchange rate for $currency1/$currency2: " . $rates['ticker']['last']);

insert_new_ticker($job, $exchange, $currency1, $currency2, array(
	"last_trade" => $rates['ticker']['last'],
	"bid" => $rates['ticker']['buy'],
	"ask" => $rates['ticker']['sell'],
	"volume" => $rates['ticker']['vol'], // last 24h
	// ignores 'high' (last 24h), 'low' (last 24h)
));
