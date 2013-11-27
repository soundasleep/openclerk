<?php

/**
 * Summary job: convert all cryptocurrencies to CNY (via BTC) using BTC China, and add any CAD balances.
 */

// get last value of all BTC
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("crypto2btc", $job['user_id']));
if ($balance = $q->fetch()) {

	// BTC is converted at BTC-e last sell rate
	// fail if there is no current rate (otherwise there is no point of this job, we don't want erraneous zero balances)
	$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
	$q->execute(array(
		"exchange" => "btcchina",
		"currency1" => "cny",
		"currency2" => "btc",
	));
	if ($ticker = $q->fetch()) {
		$total += $balance['balance'] * $ticker['sell'];
	} else {
		throw new JobException("There is no recent ticker balance for cny/btc on btcchina - cannot convert");
	}

}

// add total USD balances
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("totalcny", $job['user_id']));
if ($balance = $q->fetch()) {
	$total += $balance['balance'];
}

crypto_log("Total converted CNY BTC China balance for user " . $job['user_id'] . ": " . $total);
