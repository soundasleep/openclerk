<?php

/**
 * Summary job: convert all cryptocurrencies to BTC.
 */

// BTC is kept as-is
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("totalbtc", $job['user_id']));
if ($balance = $q->fetch()) {
	$total += $balance['balance'];
}

// LTC is converted at BTC-e ticker rate sell
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("totalltc", $job['user_id']));
if ($balance = $q->fetch()) {
	$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
	$q->execute(array(
		"exchange" => "btce",
		"currency1" => "btc",
		"currency2" => "ltc",
	));
	if ($ticker = $q->fetch()) {
		$total += $balance['balance'] * $ticker['sell'];
	}
}

// NMC is converted at BTC-e ticker rate sell
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("totalnmc", $job['user_id']));
if ($balance = $q->fetch()) {
	$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
	$q->execute(array(
		"exchange" => "btce",
		"currency1" => "btc",
		"currency2" => "nmc",
	));
	if ($ticker = $q->fetch()) {
		$total += $balance['balance'] * $ticker['sell'];
	}
}

crypto_log("Total converted BTC balance for user " . $job['user_id'] . ": " . $total);
