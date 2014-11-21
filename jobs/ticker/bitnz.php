<?php

/**
 * BitNZ ticker job.
 * Issue #328: BitNZ now has an actual ticker API that we can use
 */

$exchange_name = "bitnz";
$currency1 = 'nzd';
$currency2 = 'btc';

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://bitnz.com/api/0/ticker")));

if (!isset($rates['last'])) {
	throw new ExternalAPIException("No $currency1/$currency2 last rate for $exchange_name");
}

insert_new_ticker($job, $exchange, $currency1, $currency2, array(
	"last_trade" => $rates['last'],
	"bid" => $rates['bid'],
	"ask" => $rates['ask'],
	"volume" => $rates['volume'],
	// also 'vwap', 'high', 'low'
));
