<?php

/**
 * CEX.io balance job.
 */

$exchange = "cexio";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_cexio WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

require(__DIR__ . "/_cexio.php");

$balance = cexio_query($account['api_key'], $account['api_username'], $account['api_secret'], "https://cex.io/api/balance/");
crypto_log(print_r($balance, true));

if (isset($balance['error'])) {
	throw new ExternalAPIException(htmlspecialchars($balance['error']));
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['cexio']; // ghs, btc

foreach ($currencies as $currency) {
	if (!isset($balance[strtoupper($currency)]) || !$balance[strtoupper($currency)]) {
		crypto_log("No $exchange balance for $currency");
		continue;
	}

	$b = $balance[strtoupper($currency)]['available'] + (isset($balance[strtoupper($currency)]['orders']) ? $balance[strtoupper($currency)]['orders'] : 0);
	crypto_log($exchange . " balance for " . $currency . ": " . $b);

	insert_new_balance($job, $account, $exchange, $currency, $b);
}
