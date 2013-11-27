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

	$rates = json_decode(crypto_get_contents(crypto_wrap_url('https://data.mtgox.com/api/1/' . $rl["cur2"] . $rl["cur1"] . '/ticker')), true);
	if ($rates === null) {
		throw new ExternalAPIException("Invalid JSON detected");
	}

	if (!isset($rates['return']['avg']['value_int'])) {
		throw new ExternalAPIException("No " . $rl['cur1'] . "/" . $rl['cur2'] . " rate for " . $exchange['name']);
	}

	crypto_log($exchange['name'] . " rate for " . $rl['cur1'] . "/" . $rl['cur2'] . ": " . ($rates['return']['avg']['value_int'] / $rl['divisor']));

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
	$q = db()->prepare("INSERT INTO ticker SET is_recent=1, exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade / :divisor, buy=:buy / :divisor, sell=:sell / :divisor, volume=:volume / :vol_divisor, job_id=:job_id, is_daily_data=1");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($rl['cur1']),
		"currency2" => strtolower($rl['cur2']),
		"last_trade" => $rates['return']['last']['value_int'],
		"buy" => $rates['return']['buy']['value_int'],
		"sell" => $rates['return']['sell']['value_int'],
		"volume" => $rates['return']['vol']['value_int'],
		"job_id" => $job['id'],

		"divisor" => $rl['divisor'],
		"vol_divisor" => $rl['vol_divisor'],
	));

	crypto_log("Inserted new ticker id=" . db()->lastInsertId());
}
