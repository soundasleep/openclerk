<?php

/**
 * Summary job: convert all cryptocurrencies to AUD (via BTC) using Mt.Gox, and add any AUD balances.
 */

// get last value of all BTC
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("crypto2btc", $job['user_id']));
if ($balance = $q->fetch()) {

	// BTC is converted at BTC-e last sell rate
	$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
	$q->execute(array(
		"exchange" => "mtgox",
		"currency1" => "aud",
		"currency2" => "btc",
	));
	if ($ticker = $q->fetch()) {
		$total += $balance['balance'] * $ticker['sell'];
	}

}

// add total AUD balances
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("totalaud", $job['user_id']));
if ($balance = $q->fetch()) {
	$total += $balance['balance'];
}

crypto_log("Total converted AUD Mt.Gox balance for user " . $job['user_id'] . ": " . $total);
