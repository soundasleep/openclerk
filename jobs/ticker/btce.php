<?php

/**
 * BTC-E ticker job.
 */

$rates_list = array(
	array('cur1' => 'btc', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'btc'), // all flipped around
	array('cur1' => 'usd', 'cur2' => 'ltc'), // all flipped around
	array('cur1' => 'btc', 'cur2' => 'nmc'), // all flipped around
);

foreach ($rates_list as $rl) {
	$rates = json_decode(file_get_contents(crypto_wrap_url("https://btc-e.com/api/2/" . $rl["cur2"] . "_" . $rl["cur1"] . "/ticker")), true);
	if ($rates === null) {
		throw new ExternalAPIException("Invalid JSON detected.");
	}

	if (!isset($rates['ticker']['last'])) {
		throw new ExternalAPIException("No " . $rl['cur1'] . "/" . $rl['cur2'] . " rate for $exchange", $rates);
	}

	crypto_log($exchange['name'] . " rate for " . $rl['cur1'] . "/" . $rl['cur2'] . ": " . $rates['ticker']['last']);

	// update old recent values
	$q = db()->prepare("UPDATE ticker SET is_recent=0 WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($rl['cur1']),
		"currency2" => strtolower($rl['cur2']),
	));

	// insert in new ticker value
	$q = db()->prepare("INSERT INTO ticker SET is_recent=1, exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade, buy=:buy, sell=:sell, volume=:volume");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($rl['cur1']),
		"currency2" => strtolower($rl['cur2']),
		"last_trade" => $rates['ticker']['last'],
		"buy" => $rates['ticker']['buy'],
		"sell" => $rates['ticker']['sell'],
		"volume" => $rates['ticker']['vol_cur'],
	));

	crypto_log("Inserted new ticker id=" . db()->lastInsertId());
}
