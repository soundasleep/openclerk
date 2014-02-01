<?php

/**
 * Bitstamp ticker job.
 */

$exchange_name = "bitstamp";
$currency1 = "usd";
$currency2 = "btc";

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.bitstamp.net/api/ticker/")));

if (!isset($rates['last'])) {
	throw new ExternalAPIException("No $currency1/$currency2 last rate for $exchange_name");
}

insert_new_ticker($job, $exchange, $currency1, $currency2, array(
	"last_trade" => $rates['last'],
	"bid" => $rates['bid'],
	"ask" => $rates['ask'],
	"volume" => $rates['volume'],
));
