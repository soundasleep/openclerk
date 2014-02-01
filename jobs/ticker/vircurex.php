<?php

/**
 * Vircurex ticker job.
 */

$rates_list = array(
	// array('cur1' => 'btc', 'cur2' => 'bqc'), // all flipped around - removed in 0.6
	// array('cur1' => 'btc', 'cur2' => 'cnc'), // all flipped around - removed in 0.6
	// array('cur1' => 'btc', 'cur2' => 'yac'), // all flipped around - removed in 0.6
	array('cur1' => 'btc', 'cur2' => 'anc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'dgc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'dog'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'dvc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'frc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ftc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'i0c'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ixc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'nmc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'nvc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ppc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'trc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'xpm'), // all flipped around
	array('cur1' => 'eur', 'cur2' => 'btc'), // all flipped around
	array('cur1' => 'ltc', 'cur2' => 'nmc'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'btc'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'nmc'), // all flipped around
);

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://api.vircurex.com/api/get_info_for_currency.json")));

foreach ($rates_list as $rl) {

	if (!isset($rates[get_currency_abbr($rl['cur2'])][get_currency_abbr($rl['cur1'])])) {
		throw new ExternalAPIException("No " . $rl['cur1'] . "/" . $rl['cur2'] . " rate for Vircurex");
	}

	$obj = $rates[get_currency_abbr($rl['cur2'])][get_currency_abbr($rl['cur1'])];
	crypto_log($exchange['name'] . " rate for " . $rl['cur1'] . "/" . $rl['cur2'] . ": " . $obj['last_trade']);

	insert_new_ticker($job, $exchange, strtolower($rl['cur1']), strtolower($rl['cur2']), array(
		"last_trade" => $obj['last_trade'],
		"bid" => $obj['highest_bid'],
		"ask" => $obj['lowest_ask'],
		"volume" => $obj['volume'],
	));

}
