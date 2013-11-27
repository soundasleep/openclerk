<?php

/**
 * Cryptsy ticker job.
 */

$rates_list = array(
	array('cur1' => 'btc', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ftc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'nvc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'ppc'), // all flipped around
	// currencies not yet exposed to users or public
	array('cur1' => 'btc', 'cur2' => 'ixc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'mnc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'trc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'wdc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'xpm'), // all flipped around
	// lots of others
);

// get all the orderbook data (this is a big file!)
$rates = json_decode(crypto_get_contents(crypto_wrap_url("http://pubapi.cryptsy.com/api.php?method=marketdatav2")), true);
if ($rates === null) {
	throw new ExternalAPIException("Invalid JSON detected");
}
if (isset($rates['notice'])) {
	log_uncaught_exception(new ExternalAPIException("Notice from " . $exchange['name'] . ": " . $rates['notice']));
}

$first = true;
foreach ($rates_list as $rl) {
	$cur1 = $rl['cur1'];
	$cur2 = $rl['cur2'];
	if (!isset($rates['return']['markets'][strtoupper($cur2) . "/" . strtoupper($cur1)])) {
		throw new ExternalAPIException("No $cur2/$cur1 rate for " . $exchange['name']);
	}

	$market = $rates['return']['markets'][strtoupper($cur2) . "/" . strtoupper($cur1)];
	$data = array(
		'last_trade' => $market['lasttradeprice'],
		'volume' => $market['volume'],
		'buy' => $market['buyorders'][0]['price'],
		'sell' => $market['sellorders'][0]['price'],
		// other market data available: lasttradetime, [primary/secondary][name/code], recenttrades (array)
	);

	crypto_log($exchange['name'] . " rate for $cur1/$cur2: " . $data['last_trade']);

	// update old recent values
	$q = db()->prepare("UPDATE ticker SET is_recent=0 WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($cur1),
		"currency2" => strtolower($cur2),
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE ticker SET is_daily_data=0 WHERE is_daily_data=1 AND exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND
		date_format(created_at, '%d-%m-%Y') = date_format(now(), '%d-%m-%Y')");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($cur1),
		"currency2" => strtolower($cur2),
	));

	// insert in new ticker value
	$q = db()->prepare("INSERT INTO ticker SET is_recent=1, exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade, buy=:buy, sell=:sell, volume=:volume, job_id=:job_id, is_daily_data=1");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($cur1),
		"currency2" => strtolower($cur2),
		"last_trade" => $data['last_trade'],
		"buy" => $data['buy'],
		"sell" => $data['sell'],
		"volume" => $data['volume'],
		"job_id" => $job['id'],
	));

	crypto_log("Inserted new ticker id=" . db()->lastInsertId());
}
