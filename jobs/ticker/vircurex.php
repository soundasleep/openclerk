<?php

/**
 * Vircurex ticker job.
 */

$rates_list = array(
	array('cur1' => 'usd', 'cur2' => 'btc'), // all flipped around
	array('cur1' => 'eur', 'cur2' => 'btc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'nmc'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'nmc'), // all flipped around
	array('cur1' => 'ltc', 'cur2' => 'nmc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'trc'), // all flipped around
	// currencies not yet exposed to users or public
	// array('cur1' => 'btc', 'cur2' => 'bqc'), // all flipped around - removed in 0.6
	// array('cur1' => 'btc', 'cur2' => 'cnc'), // all flipped around - removed in 0.6
	array('cur1' => 'btc', 'cur2' => 'dvc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'frc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ftc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ixc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'nvc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ppc'), // all flipped around
	// array('cur1' => 'btc', 'cur2' => 'yac'), // all flipped around - removed in 0.6
	array('cur1' => 'btc', 'cur2' => 'xpm'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'dgc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'anc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'i0c'), // all flipped around
);

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://vircurex.com/api/get_info_for_currency.json")));

foreach ($rates_list as $rl) {

	if (!isset($rates[strtoupper($rl['cur2'])][strtoupper($rl['cur1'])])) {
		throw new ExternalAPIException("No " . $rl['cur1'] . "/" . $rl['cur2'] . " rate for Vircurex");
	}

	$obj = $rates[strtoupper($rl['cur2'])][strtoupper($rl['cur1'])];
	crypto_log($exchange['name'] . " rate for " . $rl['cur1'] . "/" . $rl['cur2'] . ": " . $obj['last_trade']);

	// update old recent values
	$q = db()->prepare("UPDATE ticker SET is_recent=0 WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($rl['cur1']),
		"currency2" => strtolower($rl['cur2']),
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE ticker SET is_daily_data=0 WHERE is_daily_data=1 AND exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND
		date_format(created_at, '%d-%m-%Y') = date_format(now(), '%d-%m-%Y')");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($rl['cur1']),
		"currency2" => strtolower($rl['cur2']),
	));

	// insert in new ticker value
	$q = db()->prepare("INSERT INTO ticker SET is_recent=1, exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade, buy=:buy, sell=:sell, volume=:volume, job_id=:job_id, is_daily_data=1");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($rl['cur1']),
		"currency2" => strtolower($rl['cur2']),
		"last_trade" => $obj['last_trade'],
		"buy" => $obj['highest_bid'],
		"sell" => $obj['lowest_ask'],
		"volume" => $obj['volume'],
		"job_id" => $job['id'],
	));

	crypto_log("Inserted new ticker id=" . db()->lastInsertId());
}
