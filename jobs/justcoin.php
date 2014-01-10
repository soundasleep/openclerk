<?php

/**
 * Justcoin balance job.
 * Uses API documentation from https://github.com/justcoin/snow/blob/master/docs/calls.md
 */

$exchange = "justcoin";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_justcoin WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// the API calls are simple ?query strings
$balances = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://justcoin.com/api/v1/balances?key=" . urlencode($account['api_key']))));
if (isset($balances['message']) && $balances['message']) {
	throw new ExternalAPIException("API returned error: '" . htmlspecialchars($balances['message']) . "'");
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['justcoin']; // supports btc, usd, eur, nok, ltc, xrp

foreach ($balances as $balance_instance) {
	foreach ($currencies as $currency) {
		if (isset($balance_instance['currency']) && strtoupper($balance_instance['currency']) == get_currency_abbr($currency)) {
			// also available: balance, hold, available
			$balance = $balance_instance['balance'];
			crypto_log($exchange . " balance for " . $currency . ": " . $balance);
			insert_new_balance($job, $account, $exchange, $currency, $balance);

		}
	}
}

// check that we don't have any other permissions
// this is expected to fail
/*
$check = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://justcoin.com/api/v1/BTC/address?key=" . urlencode($account['api_key']))));
if (!isset($check['message'])) {
	throw new ExternalAPIException("API key has Deposit permission");
}
*/
