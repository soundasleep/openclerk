<?php

/**
 * Mt.Gox ticker job.
 */

$rates_list = array(
	// see https://en.bitcoin.it/wiki/MtGox/API for divisors
	array('cur1' => 'USD', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'EUR', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	// currencies not yet exposed to users or public
	array('cur1' => 'JPY', 'cur2' => 'BTC', 'divisor' => 1e3, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'SEK', 'cur2' => 'BTC', 'divisor' => 1e3, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'AUD', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'CAD', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'CHF', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'CNY', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'DKK', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'GBP', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'HKD', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'NZD', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'PLN', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'RUB', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'SGD', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'THB', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'NOK', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
	array('cur1' => 'CZK', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
);

$first = true;
foreach ($rates_list as $rl) {
	// sleep between requests
	if (!$first) {
		set_time_limit(get_site_config('sleep_mtgox_ticker') * 2);
		sleep(get_site_config('sleep_mtgox_ticker'));
	}
	$first = false;

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url('https://data.mtgox.com/api/1/' . $rl["cur2"] . $rl["cur1"] . '/ticker')));

	if (!isset($rates['return']['avg']['value_int'])) {
		throw new ExternalAPIException("No " . $rl['cur1'] . "/" . $rl['cur2'] . " rate for " . $exchange['name']);
	}

	insert_new_ticker($job, $exchange, strtolower($rl['cur1']), strtolower($rl['cur2']), array(
		"last_trade" => $rates['return']['last']['value_int'] / $rl['divisor'],
		"bid" => $rates['return']['buy']['value_int'] / $rl['divisor'],
		"ask" => $rates['return']['sell']['value_int'] / $rl['divisor'],
		"volume" => $rates['return']['vol']['value_int'] / $rl['vol_divisor'],
	));

}
