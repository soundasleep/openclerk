<?php

/**
 * Vault of Satoshi balance job.
 */

$exchange = "vaultofsatoshi";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_vaultofsatoshi WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

require(__DIR__ . "/_vaultofsatoshi.php");

$balance = vaultofsatoshi_query($account['api_key'], $account['api_secret'], "/info/balance");
if (isset($balance['message']) && $balance['message']) {
	throw new ExternalAPIException(htmlspecialchars($balance['message']));
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['vaultofsatoshi']; // btc, usd, ...
foreach ($currencies as $currency) {
	if (!isset($balance['data'][get_currency_abbr($currency)]['value'])) {
		crypto_log("No $exchange balance for $currency");
		continue;
	}

	// we could either use 'value_int' / 10^'precision', but we are storing all
	// values as decimals and 'value' is a string representation - so we can
	// probably just use 'value' directly.
	// see https://www.vaultofsatoshi.com/api#currency_object
	$b = $balance['data'][get_currency_abbr($currency)]['value'];
	insert_new_balance($job, $account, $exchange, $currency, $b);

}
