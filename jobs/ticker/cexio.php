<?php

/**
 * CEX.io ticker job.
 */

$exchange_name = "cexio";
$currency1 = "btc";
$currency2 = "ghs";

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://cex.io/api/ticker/" . strtoupper($currency2) . "/" . strtoupper($currency1))));

if (!isset($rates['last'])) {
	throw new ExternalAPIException("No $currency1/$currency2 last rate for $exchange_name");
}

crypto_log("$exchange_name rate for $currency1/$currency2: " . $rates['last']);

insert_new_ticker($job, $exchange, $currency1, $currency2, array(
	"last_trade" => $rates['last'],
	"bid" => $rates['bid'],
	"ask" => $rates['ask'],
	"volume" => $rates['volume'], // last 24h
	// ignores 'high' (last 24h), 'low' (last 24h)
));
