<?php

/**
 * BTC-e ticker job.
 */

$rates_list = array(
	array('cur1' => 'btc', 'cur2' => 'ftc'),
	array('cur1' => 'btc', 'cur2' => 'ltc'),
	array('cur1' => 'btc', 'cur2' => 'nmc'),
	array('cur1' => 'btc', 'cur2' => 'nvc'),
	array('cur1' => 'btc', 'cur2' => 'ppc'),
	array('cur1' => 'btc', 'cur2' => 'trc'),
	array('cur1' => 'eur', 'cur2' => 'btc'),
	array('cur1' => 'eur', 'cur2' => 'ltc'),
	array('cur1' => 'usd', 'cur2' => 'btc'),
	array('cur1' => 'usd', 'cur2' => 'eur'),
	array('cur1' => 'usd', 'cur2' => 'ltc'),
	array('cur1' => 'usd', 'cur2' => 'nmc'),
	// 0.20	
	array('cur1' => 'gbp', 'cur2' => 'btc'),
	array('cur1' => 'gbp', 'cur2' => 'ltc'),
	array('cur1' => 'cnh', 'cur2' => 'btc'),
	array('cur1' => 'cnh', 'cur2' => 'ltc'),
	array('cur1' => 'cnh', 'cur2' => 'usd'),		// this is swapped around before going into DB
	array('cur1' => 'usd', 'cur2' => 'gbp'),
	array('cur1' => 'usd', 'cur2' => 'nvc'),
	// currencies not yet exposed to users or public
	array('cur1' => 'rur', 'cur2' => 'btc'),
	array('cur1' => 'rur', 'cur2' => 'ltc'),
	array('cur1' => 'rur', 'cur2' => 'usd'),
	array('cur1' => 'btc', 'cur2' => 'xpm'),
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

	// switch 'cnh' to 'cny'
	if ($rl['cur1'] == 'cnh') $rl['cur1'] = 'cny';
	if ($rl['cur2'] == 'cnh') $rl['cur2'] = 'cny';

	// switch 'cny/usd' to 'usd/cny';
	if ($rl['cur1'] == 'cny' && $rl['cur2'] == 'usd') {
		$rl['cur1'] = 'usd';
		$rl['cur2'] = 'cny';
		$rates['ticker']['last'] = 1 / $rates['ticker']['last'];
		$rates['ticker']['sell'] = 1 / $rates['ticker']['sell'];
		$rates['ticker']['buy'] = 1 / $rates['ticker']['buy'];
		// swap around
		$tmp = $rates['ticker']['sell'];
		$rates['ticker']['sell'] = $rates['ticker']['buy'];
		$rates['ticker']['buy'] = $tmp;
	}

	insert_new_ticker($job, $exchange, strtolower($rl['cur1']), strtolower($rl['cur2']), array(
		"last_trade" => $rates['ticker']['last'],
		"bid" => $rates['ticker']['sell'],
		"ask" => $rates['ticker']['buy'],
		"volume" => $rates['ticker']['vol_cur'],
	));

}
