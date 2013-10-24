<?php

/**
 * Generic crypto2X conversion.
 * Assumes that X is not 'btc' and that there exists some exchange for X to BTC.
 * Converts cryptocurrencies and commodity currencies.
 */

// LTC is kept as-is
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("total" . $currency, $job['user_id']));
if ($balance = $q->fetch()) {
	crypto_log("Initial balance: " . $balance['balance']);
	$total += $balance['balance'];
}

// BTC is converted at default ticker rate buy
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("totalbtc", $job['user_id']));
if ($balance = $q->fetch()) {
	$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
	$q->execute(array(
		"exchange" => get_default_currency_exchange($currency),
		"currency1" => "btc",
		"currency2" => $currency,
	));
	if ($ticker = $q->fetch()) {
		crypto_log("+ from BTC: " . ($balance['balance'] / $ticker['buy']));
		$total += $balance['balance'] / $ticker['buy'];
	}
}

// other cryptocurrencies are converted first to BTC, and then to the given currency
foreach (array_merge(get_all_cryptocurrencies(), get_all_commodity_currencies()) as $c) {
	if ($c == $currency || $c == 'btc') continue;

	// e.g. NMC to BTC
	$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
	$q->execute(array("total" . $c, $job['user_id']));
	if ($balance = $q->fetch()) {
		$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
		$q->execute(array(
			"exchange" => get_default_currency_exchange($c),
			"currency1" => "btc",
			"currency2" => $c,
		));
		if ($ticker = $q->fetch()) {
			$temp = $balance['balance'] * $ticker['sell'];
			crypto_log("+ from " . strtoupper($c) . " (BTC): " . ($temp));

			// and then BTC to LTC
			$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1");
			$q->execute(array(
				"exchange" => get_default_currency_exchange($currency),
				"currency1" => "btc",
				"currency2" => $currency,
			));
			if ($ticker = $q->fetch()) {
				crypto_log("+ from " . strtoupper($c) . " (" . strtoupper($currency) . "): " . ($temp / $ticker['buy']));
				$total += $temp / $ticker['buy'];
			}
		}

	}
}

crypto_log("Total converted " . strtoupper($currency) . " balance for user " . $job['user_id'] . ": " . $total);
