<?php

/**
 * Bit2c ticker job.
 */

//https://www.bit2c.co.il/Exchanges/BtcNis/Ticker.json
//https://www.bit2c.co.il/Exchanges/LtcBtc/Ticker.json
//https://www.bit2c.co.il/Exchanges/LtcNis/Ticker.json
$rates_list = array(
	// see https://en.bitcoin.it/wiki/MtGox/API for divisors
	array('cur1' => 'Nis', 'cur2' => 'Btc', 'divisor' => 1, 'vol_divisor' => 1), // all flipped around
	array('cur1' => 'Nis', 'cur2' => 'Ltc', 'divisor' => 1, 'vol_divisor' => 1), // all flipped around
	array('cur1' => 'Btc', 'cur2' => 'Ltc', 'divisor' => 1, 'vol_divisor' => 1), // all flipped around
);

$first = true;
foreach ($rates_list as $rl) {
	// sleep between requests
	if (!$first) {
		set_time_limit(get_site_config('sleep_bit2c_ticker') * 2);
		sleep(get_site_config('sleep_bit2c_ticker'));
	}
	$first = false;

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url('https://www.bit2c.co.il/Exchanges/' . $rl["cur2"] . $rl["cur1"] . '/Ticker.json')));
	
	$coin1 = strtolower($rl["cur1"]);
	$coin2 = strtolower($rl["cur2"]);

	if ($coin1 == 'nis') {
		$coin1 = 'ils';
	}

	if (!isset($rates['ll'])) {
		throw new ExternalAPIException("No " . $coin1 . "/" . $coin2 . " rate for " . $exchange['name']);
	}

	insert_new_ticker($job, $exchange, $coin1, $coin2, array(
		"last_trade" => $rates['ll'] / $rl['divisor'],
		"bid" => $rates['h'] / $rl['divisor'],
		"ask" => $rates['l'] / $rl['divisor'],
		"volume" => $rates['a'] / $rl['vol_divisor'],
	));

}
