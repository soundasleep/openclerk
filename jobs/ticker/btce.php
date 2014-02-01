<?php

/**
 * BTC-e ticker job.
 */

$rates_list = array(
	array('cur1' => 'usd', 'cur2' => 'btc'), // all flipped around
	array('cur1' => 'eur', 'cur2' => 'btc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'eur', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'nmc'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'nmc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ftc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ppc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'nvc'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'eur'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'trc'), // all flipped around
	// currencies not yet exposed to users or public
	array('cur1' => 'rur', 'cur2' => 'btc'), // all flipped around
	array('cur1' => 'rur', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'rur', 'cur2' => 'usd'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'xpm'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'nvc'), // all flipped around
);

$first = true;
foreach ($rates_list as $rl) {
	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_btce_ticker') * 2));
		sleep(get_site_config('sleep_btce_ticker'));
	}
	$first = false;

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://btc-e.com/api/2/" . $rl["cur2"] . "_" . $rl["cur1"] . "/ticker")));

	if (!isset($rates['ticker']['last'])) {
		if (isset($rates['error'])) {
			throw new ExternalAPIException("Could not find " . $rl['cur1'] . "/" . $rl['cur2'] . " rate for $exchange: " . htmlspecialchars($rates['error']));
		}

		throw new ExternalAPIException("No " . $rl['cur1'] . "/" . $rl['cur2'] . " rate for $exchange");
	}

	insert_new_ticker($job, $exchange, strtolower($rl['cur1']), strtolower($rl['cur2']), array(
		"last_trade" => $rates['ticker']['last'],
		"bid" => $rates['ticker']['sell'],
		"ask" => $rates['ticker']['buy'],
		"volume" => $rates['ticker']['vol_cur'],
	));

}
