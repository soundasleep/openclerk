<?php

/**
 * Kraken balance job.
 */

$exchange = "kraken";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_kraken WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

require(__DIR__ . "/_kraken.php");

$balance = kraken_query($account['api_key'], $account['api_secret'], "/0/private/Balance");
crypto_log(print_r($balance, true));
if (isset($balance['error']) && $balance['error']) {
	throw new ExternalAPIException(htmlspecialchars($balance['error'][0]));
}

foreach ($balance['result'] as $iso4 => $value) {
	$currency = map_iso4_name($iso4);
	if (!$currency) {
		crypto_log("Unknown iso4 name '$cur'");
		continue;
	}
	insert_new_balance($job, $account, $exchange, $currency, $value);
}
