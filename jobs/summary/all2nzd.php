<?php

/**
 * Summary job: convert all cryptocurrencies to NZD (via BTC).
 */

// get last value of all BTC
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("crypto2btc", $job['user_id']));
if ($balance = $q->fetch()) {

	// BTC is converted at BitNZ last sell rate
	$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
	$q->execute(array(
		"exchange" => "bitnz",
		"currency1" => "nzd",
		"currency2" => "btc",
	));
	if ($ticker = $q->fetch()) {
		$total += $balance['balance'] * $ticker['sell'];
	}

}

crypto_log("Total converted NZD balance for user " . $job['user_id'] . ": " . $total);
