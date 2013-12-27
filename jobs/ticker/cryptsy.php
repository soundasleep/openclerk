<?php

/**
 * Cryptsy ticker job.
 */

$rates_list = array(
	array('cur1' => 'btc', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ftc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'nvc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ppc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'trc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'dog'), // all flipped around
	// currencies not yet exposed to users or public
	array('cur1' => 'btc', 'cur2' => 'ixc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'mnc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'wdc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'xpm'), // all flipped around
	// lots of others
);

// get all the orderbook data (this is a big file!)
$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://pubapi.cryptsy.com/api.php?method=marketdatav2")));
if (isset($rates['notice'])) {
	log_uncaught_exception(new ExternalAPIException("Notice from " . $exchange['name'] . ": " . $rates['notice']));
}

$first = true;
foreach ($rates_list as $rl) {
	$cur1 = $rl['cur1'];
	$cur2 = $rl['cur2'];
	if (!isset($rates['return']['markets'][get_currency_abbr($cur2) . "/" . get_currency_abbr($cur1)])) {
		throw new ExternalAPIException("No $cur2/$cur1 rate for " . $exchange['name']);
	}

	$market = $rates['return']['markets'][get_currency_abbr($cur2) . "/" . get_currency_abbr($cur1)];
	$data = array(
		'last_trade' => $market['lasttradeprice'],
		'volume' => $market['volume'],
		'buy' => $market['buyorders'][0]['price'],
		'sell' => $market['sellorders'][0]['price'],
		// other market data available: lasttradetime, [primary/secondary][name/code], recenttrades (array)
	);

	crypto_log($exchange['name'] . " rate for $cur1/$cur2: " . $data['last_trade']);

	insert_new_ticker($job, $exchange, strtolower($cur1), strtolower($cur2), array(
		"last_trade" => $data['last_trade'],
		"bid" => $data['buy'],
		"ask" => $data['sell'],
		"volume" => $data['volume'],
	));

}
