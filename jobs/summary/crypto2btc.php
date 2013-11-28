<?php

/**
 * Summary job: convert all cryptocurrencies to BTC.
 */
$currency = 'btc';

// BTC is kept as-is
$q = db()->prepare("SELECT * FROM summary_instances WHERE summary_type=? AND user_id=? AND is_recent=1");
$q->execute(array("total" . $currency, $job['user_id']));
if ($balance = $q->fetch()) {
	crypto_log("Initial balance (" . strtoupper($currency) . "): " . $balance['balance']);
	$total += $balance['balance'];
}

// convert all cryptocurrencies as per get_default_currency_exchange()
foreach (get_all_cryptocurrencies() as $c) {
	if ($c == $currency) continue;

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
			crypto_log("+ from " . strtoupper($c) . " (" . strtoupper($currency) . "): " . ($temp));
			$total += $temp;
		} else {
			crypto_log("No $exchange ticket found for btc/$c");
		}
	} else {
		crypto_log("No balance found for currency '$c'");
	}
}

crypto_log("Total converted BTC balance for user " . $job['user_id'] . ": " . $total);
