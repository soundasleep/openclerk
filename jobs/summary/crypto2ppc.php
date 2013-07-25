<?php

/**
 * Summary job: convert all cryptocurrencies to PPC.
 */

// PPC is kept as-is
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("totalppc", $job['user_id']));
if ($balance = $q->fetch()) {
	$total += $balance['balance'];
}

// BTC is converted at BTC-e ticker rate buy
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("totalbtc", $job['user_id']));
if ($balance = $q->fetch()) {
	$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
	$q->execute(array(
		"exchange" => "btce",
		"currency1" => "btc",
		"currency2" => "ppc",
	));
	if ($ticker = $q->fetch()) {
		crypto_log("+ from BTC: " . ($balance['balance'] / $ticker['buy']));
		$total += $balance['balance'] / $ticker['buy'];
	}
}

// LTC is first converted to BTC then converted to PPC at BTC-e ticker rate sell
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
		$temp = $balance['balance'] * $ticker['sell'];
		crypto_log("+ from LTC (BTC): " . ($temp));

		$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
		$q->execute(array(
			"exchange" => "btce",
			"currency1" => "btc",
			"currency2" => "ppc",
		));
		if ($ticker = $q->fetch()) {
			crypto_log("+ from LTC (PPC): " . ($temp / $ticker['buy']));
			$total += $temp / $ticker['buy'];
		}
	}

}

// NMC is first converted to BTC then converted to PPC at BTC-e ticker rate sell
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("totalltc", $job['user_id']));
if ($balance = $q->fetch()) {
	$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
	$q->execute(array(
		"exchange" => "btce",
		"currency1" => "btc",
		"currency2" => "nmc",
	));
	if ($ticker = $q->fetch()) {
		$temp = $balance['balance'] * $ticker['sell'];
		crypto_log("+ from NMC (BTC): " . ($temp));

		$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
		$q->execute(array(
			"exchange" => "btce",
			"currency1" => "btc",
			"currency2" => "ppc",
		));
		if ($ticker = $q->fetch()) {
			crypto_log("+ from NMC (PPC): " . ($temp / $ticker['buy']));
			$total += $temp / $ticker['buy'];
		}
	}

}

// FTC is first converted to BTC then converted to PPC at BTC-e ticker rate sell
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("totalftc", $job['user_id']));
if ($balance = $q->fetch()) {
	$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
	$q->execute(array(
		"exchange" => "btce",
		"currency1" => "btc",
		"currency2" => "ftc",
	));
	if ($ticker = $q->fetch()) {
		$temp = $balance['balance'] * $ticker['sell'];
		crypto_log("+ from FTC (BTC): " . ($temp));

		$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
		$q->execute(array(
			"exchange" => "btce",
			"currency1" => "btc",
			"currency2" => "ppc",
		));
		if ($ticker = $q->fetch()) {
			crypto_log("+ from FTC (PPC): " . ($temp / $ticker['buy']));
			$total += $temp / $ticker['buy'];
		}
	}

}

crypto_log("Total converted PPC balance for user " . $job['user_id'] . ": " . $total);
