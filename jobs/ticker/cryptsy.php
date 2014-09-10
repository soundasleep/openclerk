<?php

/**
 * Cryptsy ticker job.
 */

$exchange_name = "cryptsy";
$exchange_pairs = get_exchange_pairs();
$rates_list = $exchange_pairs['cryptsy'];

// get all the orderbook data (this is a big file!)
$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://pubapi.cryptsy.com/api.php?method=marketdatav2")));
if (isset($rates['notice'])) {
	log_uncaught_exception(new ExternalAPIException("Notice from " . $exchange['name'] . ": " . $rates['notice']));
}

$first = true;
foreach ($rates_list as $rl) {
	$cur1 = $rl[0];
	$cur2 = $rl[1];
	if (!isset($rates['return']['markets'][get_currency_abbr($cur2) . "/" . get_currency_abbr($cur1)])) {
		throw new ExternalAPIException("No $cur2/$cur1 rate for " . $exchange['name']);
	}

	$market = $rates['return']['markets'][get_currency_abbr($cur2) . "/" . get_currency_abbr($cur1)];
	$data = array(
		'last_trade' => $market['lasttradeprice'],
		'volume' => $market['volume'],
		// Cryptsy returns buy/sell incorrectly
		'bid' => $market['buyorders'][0]['price'],
		'ask' => $market['sellorders'][0]['price'],
		// other market data available: lasttradetime, [primary/secondary][name/code], recenttrades (array)
	);

	insert_new_ticker($job, $exchange, strtolower($cur1), strtolower($cur2), array(
		"last_trade" => $data['last_trade'],
		"bid" => $data['bid'],
		"ask" => $data['ask'],
		"volume" => $data['volume'],
	));

}
