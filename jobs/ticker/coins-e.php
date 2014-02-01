<?php

/**
 * Coins-E ticker job.
 */

$rates_list = array(
	// not all coins are tracked
	array('btc', 'xpm'),
	array('btc', 'trc'),
	array('btc', 'wdc'),
	array('btc', 'dog'),
	array('btc', 'ftc'),
	array('btc', 'ltc'),
	array('btc', 'ppc'),

	array('ltc', 'xpm'),

	array('xpm', 'ppc'),
);

// get the ticker
$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.coins-e.com/api/v2/markets/data/")));
foreach ($rates_list as $rl) {
	$cur1 = $rl[0];
	$cur2 = $rl[1];
	$exchange_name = $exchange['name'];

	$key = strtoupper(get_currency_abbr($cur2) . "_" . get_currency_abbr($cur1));

	if (!isset($rates['markets'][$key]['marketstat']['ltp'])) {
		throw new ExternalAPIException("No $cur1/$cur2 rate for $exchange_name");
	}

	$last = $rates['markets'][$key]['marketstat']['ltp'];
	$bid = $rates['markets'][$key]['marketstat']['bid'];
	$ask = $rates['markets'][$key]['marketstat']['ask'];
	$volume = $rates['markets'][$key]['marketstat']['24h']['volume'];

	insert_new_ticker($job, $exchange, strtolower($cur1), strtolower($cur2), array(
		"last_trade" => $last,
		"bid" => $bid,
		"ask" => $ask,
		"volume" => $volume,
		// ignoring 24h low, high, average rate etc
	));

}
