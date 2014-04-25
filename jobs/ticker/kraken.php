<?php

/**
 * Kraken balance job.
 */

$get_exchange_pairs = get_exchange_pairs();
$pairs = $get_exchange_pairs['kraken']; // (usd, btc), ...
$exchange_name = "kraken";

require(__DIR__ . "/../_kraken.php");

// unfortunately there's no obvious way that these codes are sorted, so
// we need to list them all manually
// key => should we inverse the result?
$kraken_pairs = array(
	'ltcdog' => true,
	'ltcxrp' => true,
	'ltceur' => false,
	'ltckrw' => false,
	'ltcusd' => false,
	'nmcdog' => true,
	'nmcxrp' => true,
	'nmceur' => false,
	'nmckrw' => false,
	'nmcusd' => false,
	'btcltc' => true,
	'btcnmc' => true,
	'btcdog' => true,
	'btcxrp' => true,
	'btceur' => false,
	'btckrw' => false,
	'btcusd' => false,
	'eurdog' => true,
	'eurxrp' => true,
	'krwxrp' => true,
	'usddog' => true,
	'usdxrp' => true,
);

// convert the pairs into the weird ISO4 codes used by Kraken
$map = array();
foreach ($pairs as $pair) {
	// assumes no fiat/fiat pairs
	if (isset($kraken_pairs[$pair[0] . $pair[1]])) {
		$key = strtoupper(get_iso4_name($pair[0]) . get_iso4_name($pair[1]));
		$pair['flip'] = $kraken_pairs[$pair[0] . $pair[1]];
	} else if (isset($kraken_pairs[$pair[1] . $pair[0]])) {
		$key = strtoupper(get_iso4_name($pair[1]) . get_iso4_name($pair[0]));
		$pair['flip'] = $kraken_pairs[$pair[1] . $pair[0]];
	} else {
		throw new JobException("Pair " . implode("/", $pair) . " was not found in kraken_pairs");
	}
	$map[$key] = $pair;
}

// unfortunately if we provide it a single invalid asset pair, the entire query will crash with 'Unknown asset pair'
// so we go through each pair individually
$first = true;
foreach ($map as $key => $pair) {
	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_kraken_ticker') * 2));
		sleep(get_site_config('sleep_kraken_ticker'));
	}
	$first = false;

	$ticker = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://api.kraken.com/0/public/Ticker?pair=" . $key)));

	if (isset($ticker['error'][0]) && $ticker['error'][0]) {
		$error = $pair[0] . "/" . $pair[1] . " (" . $key . ") returned " . $ticker['error'][0];
		throw new ExternalAPIException($error);
	}

	// thanks for obsfucating, Kraken!
	$ask = $ticker['result'][$key]['a'][0];		// '1' is lot volume
	$bid = $ticker['result'][$key]['b'][0];
	$last_trade = $ticker['result'][$key]['c'][0];
	$volume = $ticker['result'][$key]['v'][0];
	$volume_24h = $ticker['result'][$key]['v'][1];
	// p = volume weighted average price array(<today>, <last 24 hours>),
	// t = number of trades array(<today>, <last 24 hours>),
	// l = low array(<today>, <last 24 hours>),
	// h = high array(<today>, <last 24 hours>),
	// o = today's opening price

	// and perform some logic to get flipped pairs inserted correctly
	if ($pair['flip']) {
		if ($ask != 0) $ask = 1 / $ask;
		if ($bid != 0) $bid = 1 / $bid;
		$temp = $ask;
		$ask = $bid;
		$bid = $temp;
		if ($last_trade != 0) $last_trade = 1 / $last_trade;
	}

	crypto_log($pair[0] . "/" . $pair[1] . ": ask = $ask, bid = $bid, last_trade = $last_trade, vol = $volume_24h");

	insert_new_ticker($job, $exchange, $pair[0], $pair[1], array(
		"last_trade" => $last_trade,
		"bid" => $bid,
		"ask" => $ask,
		"volume" => $volume_24h,
	));
}
