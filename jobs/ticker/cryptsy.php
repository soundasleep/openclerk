<?php

/**
 * Cryptsy ticker job.
 */

$rates_list = array(
	array('cur1' => 'btc', 'cur2' => 'ltc'),
	array('cur1' => 'btc', 'cur2' => 'ftc'),
	array('cur1' => 'btc', 'cur2' => 'nvc'),
	array('cur1' => 'btc', 'cur2' => 'ppc'),
	array('cur1' => 'btc', 'cur2' => 'trc'),
	array('cur1' => 'btc', 'cur2' => 'dog'),
	array('cur1' => 'btc', 'cur2' => 'mec'),
	array('cur1' => 'ltc', 'cur2' => 'mec'),
	array('cur1' => 'btc', 'cur2' => 'dgc'),
	array('cur1' => 'ltc', 'cur2' => 'dgc'),
	array('cur1' => 'btc', 'cur2' => 'wdc'),
	// 0.20
	array('cur1' => 'btc', 'cur2' => 'nmc'),
	// currencies not yet exposed to users or public
	array('cur1' => 'btc', 'cur2' => 'ixc'),
	array('cur1' => 'btc', 'cur2' => 'mnc'),
	array('cur1' => 'btc', 'cur2' => 'xpm'),
	array('cur1' => 'usd', 'cur2' => 'btc'),
	array('cur1' => 'usd', 'cur2' => 'ftc'),
	array('cur1' => 'usd', 'cur2' => 'ltc'),
	array('cur1' => 'usd', 'cur2' => 'dog'),
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
