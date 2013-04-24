<?php

/**
 * BTC-E ticker job.
 */

$rates_list = array(
	array('cur1' => 'USD', 'cur2' => 'BTC', 'divisor' => 1e5, 'vol_divisor' => 1e8), // all flipped around
);

$first = true;
foreach ($rates_list as $rl) {
	// sleep between requests
	if (!$first) {
		sleep(get_site_config('sleep_mtgox_ticker'));
	}
	$first = false;

	$rates = json_decode(crypto_get_contents(crypto_wrap_url('https://data.mtgox.com/api/1/' . $rl["cur2"] . $rl["cur1"] . '/ticker')), true);
	if ($rates === null) {
		throw new ExternalAPIException("Invalid JSON detected (null).");
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

	// insert in new ticker value
	$q = db()->prepare("INSERT INTO ticker SET is_recent=1, exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade / :divisor, buy=:buy / :divisor, sell=:sell / :divisor, volume=:volume / :vol_divisor");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($rl['cur1']),
		"currency2" => strtolower($rl['cur2']),
		"last_trade" => $rates['return']['last']['value_int'],
		"buy" => $rates['return']['buy']['value_int'],
		"sell" => $rates['return']['sell']['value_int'],
		"volume" => $rates['return']['vol']['value_int'],

		"divisor" => $rl['divisor'],
		"vol_divisor" => $rl['vol_divisor'],
	));

	crypto_log("Inserted new ticker id=" . db()->lastInsertId());
}
